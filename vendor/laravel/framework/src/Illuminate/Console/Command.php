<?php
/**
 * Illuminate，控制台，命令
 */

namespace Illuminate\Console;

use Illuminate\Console\View\Components\Factory;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Traits\Macroable;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends SymfonyCommand
{
    use Concerns\CallsCommands,
        Concerns\HasParameters,
        Concerns\InteractsWithIO,
        Concerns\InteractsWithSignals,
        Concerns\PromptsForMissingInput,
        Macroable;

    /**
     * The Laravel application instance.
	 * Laravel应用实例
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $laravel;

    /**
     * The name and signature of the console command.
	 * console命令的名称和签名
     *
     * @var string
     */
    protected $signature;

    /**
     * The console command name.
	 * 控制台命令名称
     *
     * @var string
     */
    protected $name;

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string|null
     */
    protected $description;

    /**
     * The console command help text.
	 * 控制台命令帮助文本
     *
     * @var string
     */
    protected $help;

    /**
     * Indicates whether the command should be shown in the Artisan command list.
	 * 指示该命令是否应该显示在Artisan命令列表中
     *
     * @var bool
     */
    protected $hidden = false;

    /**
     * Create a new console command instance.
     *
     * @return void
     */
    public function __construct()
    {
        // We will go ahead and set the name, description, and parameters on console
        // commands just to make things a little easier on the developer. This is
        // so they don't have to all be manually specified in the constructors.
		// 我们将继续在控制台上设置名称、描述和参数，只是为了让开发人员更容易一些。
        if (isset($this->signature)) {
            $this->configureUsingFluentDefinition();
        } else {
            parent::__construct($this->name);
        }

        // Once we have constructed the command, we'll set the description and other
        // related properties of the command. If a signature wasn't used to build
        // the command we'll set the arguments and the options on this command.
		// 一旦我们构造了命令，我们将设置描述和其他命令的相关属性。
        if (! isset($this->description)) {
            $this->setDescription((string) static::getDefaultDescription());
        } else {
            $this->setDescription((string) $this->description);
        }

        $this->setHelp((string) $this->help);

        $this->setHidden($this->isHidden());

        if (! isset($this->signature)) {
            $this->specifyParameters();
        }

        if ($this instanceof Isolatable) {
            $this->configureIsolation();
        }
    }

    /**
     * Configure the console command using a fluent definition.
	 * 使用连贯的定义配置console命令
     *
     * @return void
     */
    protected function configureUsingFluentDefinition()
    {
        [$name, $arguments, $options] = Parser::parse($this->signature);

        parent::__construct($this->name = $name);

        // After parsing the signature we will spin through the arguments and options
        // and set them on this command. These will already be changed into proper
        // instances of these "InputArgument" and "InputOption" Symfony classes.
		// 解析完签名后，我们将浏览参数和选项
        $this->getDefinition()->addArguments($arguments);
        $this->getDefinition()->addOptions($options);
    }

    /**
     * Configure the console command for isolation.
	 * 配置console命令进行隔离
     *
     * @return void
     */
    protected function configureIsolation()
    {
        $this->getDefinition()->addOption(new InputOption(
            'isolated',
            null,
            InputOption::VALUE_OPTIONAL,
            'Do not run the command if another instance of the command is already running',
            false
        ));
    }

    /**
     * Run the console command.
	 * 执行console命令
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $this->laravel->make(
            OutputStyle::class, ['input' => $input, 'output' => $output]
        );

        $this->components = $this->laravel->make(Factory::class, ['output' => $this->output]);

        try {
            return parent::run(
                $this->input = $input, $this->output
            );
        } finally {
            $this->untrap();
        }
    }

    /**
     * Execute the console command.
	 * 执行console命令
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this instanceof Isolatable && $this->option('isolated') !== false &&
            ! $this->commandIsolationMutex()->create($this)) {
            $this->comment(sprintf(
                'The [%s] command is already running.', $this->getName()
            ));

            return (int) (is_numeric($this->option('isolated'))
                        ? $this->option('isolated')
                        : self::SUCCESS);
        }

        $method = method_exists($this, 'handle') ? 'handle' : '__invoke';

        try {
            return (int) $this->laravel->call([$this, $method]);
        } finally {
            if ($this instanceof Isolatable && $this->option('isolated') !== false) {
                $this->commandIsolationMutex()->forget($this);
            }
        }
    }

    /**
     * Get a command isolation mutex instance for the command.
	 * 获取命令的命令隔离互斥实例
     *
     * @return \Illuminate\Console\CommandMutex
     */
    protected function commandIsolationMutex()
    {
        return $this->laravel->bound(CommandMutex::class)
            ? $this->laravel->make(CommandMutex::class)
            : $this->laravel->make(CacheCommandMutex::class);
    }

    /**
     * Resolve the console command instance for the given command.
	 * 解析给定命令的控制台命令实例
     *
     * @param  \Symfony\Component\Console\Command\Command|string  $command
     * @return \Symfony\Component\Console\Command\Command
     */
    protected function resolveCommand($command)
    {
        if (! class_exists($command)) {
            return $this->getApplication()->find($command);
        }

        $command = $this->laravel->make($command);

        if ($command instanceof SymfonyCommand) {
            $command->setApplication($this->getApplication());
        }

        if ($command instanceof self) {
            $command->setLaravel($this->getLaravel());
        }

        return $command;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * {@inheritdoc}
     */
    public function setHidden(bool $hidden = true): static
    {
        parent::setHidden($this->hidden = $hidden);

        return $this;
    }

    /**
     * Get the Laravel application instance.
	 * 获取Laravel应用程序实例
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    public function getLaravel()
    {
        return $this->laravel;
    }

    /**
     * Set the Laravel application instance.
	 * 设置Laravel应用实例
     *
     * @param  \Illuminate\Contracts\Container\Container  $laravel
     * @return void
     */
    public function setLaravel($laravel)
    {
        $this->laravel = $laravel;
    }
}
