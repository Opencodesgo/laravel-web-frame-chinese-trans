<?php
/**
 * Illuminate，基础，控制台，Cli转存
 */

namespace Illuminate\Foundation\Console;

use Illuminate\Foundation\Concerns\ResolvesDumpSource;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper as BaseCliDumper;
use Symfony\Component\VarDumper\VarDumper;

class CliDumper extends BaseCliDumper
{
    use ResolvesDumpSource;

    /**
     * The base path of the application.
	 * 应用的基本路径
     *
     * @var string
     */
    protected $basePath;

    /**
     * The output instance.
	 * 输出实例
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * The compiled view path for the application.
	 * 应用程序的编译视图路径
     *
     * @var string
     */
    protected $compiledViewPath;

    /**
     * If the dumper is currently dumping.
	 * 如果转储程序当前正在转储
     *
     * @var bool
     */
    protected $dumping = false;

    /**
     * Create a new CLI dumper instance.
	 * 创建一个新的CLI转储实例
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @param  string  $basePath
     * @param  string  $compiledViewPath
     * @return void
     */
    public function __construct($output, $basePath, $compiledViewPath)
    {
        parent::__construct();

        $this->basePath = $basePath;
        $this->output = $output;
        $this->compiledViewPath = $compiledViewPath;
    }

    /**
     * Create a new CLI dumper instance and register it as the default dumper.
	 * 创建一个新的CLI转储程序实例，并将其注册为默认转储程序。
     *
     * @param  string  $basePath
     * @param  string  $compiledViewPath
     * @return void
     */
    public static function register($basePath, $compiledViewPath)
    {
        $cloner = tap(new VarCloner())->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO);

        $dumper = new static(new ConsoleOutput(), $basePath, $compiledViewPath);

        VarDumper::setHandler(fn ($value) => $dumper->dumpWithSource($cloner->cloneVar($value)));
    }

    /**
     * Dump a variable with its source file / line.
	 * 转储变量及其源文件/行
     *
     * @param  \Symfony\Component\VarDumper\Cloner\Data  $data
     * @return void
     */
    public function dumpWithSource(Data $data)
    {
        if ($this->dumping) {
            $this->dump($data);

            return;
        }

        $this->dumping = true;

        $output = (string) $this->dump($data, true);
        $lines = explode("\n", $output);

        $lines[0] .= $this->getDumpSourceContent();

        $this->output->write(implode("\n", $lines));

        $this->dumping = false;
    }

    /**
     * Get the dump's source console content.
	 * 获取转储的源控制台内容
     *
     * @return string
     */
    protected function getDumpSourceContent()
    {
        if (is_null($dumpSource = $this->resolveDumpSource())) {
            return '';
        }

        [$file, $relativeFile, $line] = $dumpSource;

        $href = $this->resolveSourceHref($file, $line);

        return sprintf(
            ' <fg=gray>// <fg=gray%s>%s%s</></>',
            is_null($href) ? '' : ";href=$href",
            $relativeFile,
            is_null($line) ? '' : ":$line"
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function supportsColors(): bool
    {
        return $this->output->isDecorated();
    }
}
