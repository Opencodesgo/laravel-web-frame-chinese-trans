<?php
/**
 * Illuminate，基础，控制台，make:test 任务生成命令
 */

namespace Illuminate\Foundation\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'make:test')]
class TestMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
	 * 控制台命令名称
     *
     * @var string
     */
    protected $name = 'make:test';

    /**
     * The name of the console command.
	 * 控制台命令的名称
     *
     * This name is used to identify the command during lazy loading.
	 * 此名称用于在惰性加载期间识别命令
     *
     * @var string|null
     *
     * @deprecated
     */
    protected static $defaultName = 'make:test';

    /**
     * The console command description.
	 * 控制台命令说明
     *
     * @var string
     */
    protected $description = 'Create a new test class';

    /**
     * The type of class being generated.
	 * 生成的类的类型
     *
     * @var string
     */
    protected $type = 'Test';

    /**
     * Get the stub file for the generator.
	 * 获取生成器的存根文件
     *
     * @return string
     */
    protected function getStub()
    {
        $suffix = $this->option('unit') ? '.unit.stub' : '.stub';

        return $this->option('pest')
            ? $this->resolveStubPath('/stubs/pest'.$suffix)
            : $this->resolveStubPath('/stubs/test'.$suffix);
    }

    /**
     * Resolve the fully-qualified path to the stub.
	 * 解析到存根的全限定路径
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
                        ? $customPath
                        : __DIR__.$stub;
    }

    /**
     * Get the destination class path.
	 * 获取目标类路径
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return base_path('tests').str_replace('\\', '/', $name).'.php';
    }

    /**
     * Get the default namespace for the class.
	 * 获取类的默认名称空间
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        if ($this->option('unit')) {
            return $rootNamespace.'\Unit';
        } else {
            return $rootNamespace.'\Feature';
        }
    }

    /**
     * Get the root namespace for the class.
	 * 获取类的根命名空间
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return 'Tests';
    }

    /**
     * Get the console command options.
	 * 获取控制台命令选项
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the test already exists'],
            ['unit', 'u', InputOption::VALUE_NONE, 'Create a unit test'],
            ['pest', 'p', InputOption::VALUE_NONE, 'Create a Pest test'],
        ];
    }

    /**
     * Interact further with the user if they were prompted for missing arguments.
	 * 如果提示用户输入缺少的参数，则与用户进一步交互。
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output)
    {
        if ($this->isReservedName($this->getNameInput()) || $this->didReceiveOptions($input)) {
            return;
        }

        $type = $this->components->choice('Which type of test would you like', [
            'feature',
            'unit',
            'pest feature',
            'pest unit',
        ], default: 0);

        match ($type) {
            'feature' => null,
            'unit' => $input->setOption('unit', true),
            'pest feature' => $input->setOption('pest', true),
            'pest unit' => tap($input)->setOption('pest', true)->setOption('unit', true),
        };
    }
}
