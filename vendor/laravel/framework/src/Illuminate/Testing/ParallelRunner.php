<?php
/**
 * Illuminate，测试，并行运行
 */

namespace Illuminate\Testing;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\ParallelTesting;
use ParaTest\Runners\PHPUnit\Options;
use ParaTest\Runners\PHPUnit\RunnerInterface;
use ParaTest\Runners\PHPUnit\WrapperRunner;
use PHPUnit\TextUI\XmlConfiguration\PhpHandler;
use RuntimeException;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ParallelRunner implements RunnerInterface
{
    /**
     * The application resolver callback.
	 * 应用程序解析器回调
     *
     * @var \Closure|null
     */
    protected static $applicationResolver;

    /**
     * The runner resolver callback.
	 * 跑步者解析器回调
     *
     * @var \Closure|null
     */
    protected static $runnerResolver;

    /**
     * The original test runner options.
	 * 原始的测试运行器选项
     *
     * @var \ParaTest\Runners\PHPUnit\Options
     */
    protected $options;

    /**
     * The output instance.
	 * 输出实例
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * The original test runner.
	 * 原来的试车员
     *
     * @var \ParaTest\Runners\PHPUnit\RunnerInterface
     */
    protected $runner;

    /**
     * Creates a new test runner instance.
	 * 创建一个新的测试运行器实例
     *
     * @param  \ParaTest\Runners\PHPUnit\Options  $options
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    public function __construct(Options $options, OutputInterface $output)
    {
        $this->options = $options;

        if ($output instanceof ConsoleOutput) {
            $output = new ParallelConsoleOutput($output);
        }

        $runnerResolver = static::$runnerResolver ?: function (Options $options, OutputInterface $output) {
            return new WrapperRunner($options, $output);
        };

        $this->runner = call_user_func($runnerResolver, $options, $output);
    }

    /**
     * Set the application resolver callback.
	 * 设置应用程序解析器回调
     *
     * @param  \Closure|null  $resolver
     * @return void
     */
    public static function resolveApplicationUsing($resolver)
    {
        static::$applicationResolver = $resolver;
    }

    /**
     * Set the runner resolver callback.
	 * 设置运行器解析器回调
     *
     * @param  \Closure|null  $resolver
     * @return void
     */
    public static function resolveRunnerUsing($resolver)
    {
        static::$runnerResolver = $resolver;
    }

    /**
     * Runs the test suite.
	 * 运行测试套件
     *
     * @return void
     */
    public function run(): void
    {
        (new PhpHandler)->handle($this->options->configuration()->php());

        $this->forEachProcess(function () {
            ParallelTesting::callSetUpProcessCallbacks();
        });

        try {
            $this->runner->run();
        } finally {
            $this->forEachProcess(function () {
                ParallelTesting::callTearDownProcessCallbacks();
            });
        }
    }

    /**
     * Returns the highest exit code encountered throughout the course of test execution.
	 * 返回整个测试执行过程中遇到的最高退出码
     *
     * @return int
     */
    public function getExitCode(): int
    {
        return $this->runner->getExitCode();
    }

    /**
     * Apply the given callback for each process.
	 * 为每个进程应用给定的回调
     *
     * @param  callable  $callback
     * @return void
     */
    protected function forEachProcess($callback)
    {
        collect(range(1, $this->options->processes()))->each(function ($token) use ($callback) {
            tap($this->createApplication(), function ($app) use ($callback, $token) {
                ParallelTesting::resolveTokenUsing(function () use ($token) {
                    return $token;
                });

                $callback($app);
            })->flush();
        });
    }

    /**
     * Creates the application.
	 * 创建应用程序
     *
     * @return \Illuminate\Contracts\Foundation\Application
     *
     * @throws \RuntimeException
     */
    protected function createApplication()
    {
        $applicationResolver = static::$applicationResolver ?: function () {
            if (trait_exists(\Tests\CreatesApplication::class)) {
                $applicationCreator = new class
                {
                    use \Tests\CreatesApplication;
                };

                return $applicationCreator->createApplication();
            } elseif (file_exists(getcwd().'/bootstrap/app.php')) {
                $app = require getcwd().'/bootstrap/app.php';

                $app->make(Kernel::class)->bootstrap();

                return $app;
            }

            throw new RuntimeException('Parallel Runner unable to resolve application.');
        };

        return call_user_func($applicationResolver);
    }
}
