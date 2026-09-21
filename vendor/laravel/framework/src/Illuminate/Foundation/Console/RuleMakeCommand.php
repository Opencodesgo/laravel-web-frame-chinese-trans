<?php
/**
 * Illuminate，基础，控制台，make:rule 规则创建命令
 */

namespace Illuminate\Foundation\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:rule')]
class RuleMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
	 * 控制台命令名称
     *
     * @var string
     */
    protected $name = 'make:rule';

    /**
     * The name of the console command.
	 * 控制台命令名称
     *
     * This name is used to identify the command during lazy loading.
	 * 此名称用于在惰性加载期间识别命令
     *
     * @var string|null
     *
     * @deprecated
     */
    protected static $defaultName = 'make:rule';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Create a new validation rule';

    /**
     * The type of class being generated.
	 * 生成的类的类型
     *
     * @var string
     */
    protected $type = 'Rule';

    /**
     * Build the class with the given name.
	 * 用给定的名称构建类
     *
     * @param  string  $name
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name)
    {
        return str_replace(
            '{{ ruleType }}',
            $this->option('implicit') ? 'ImplicitRule' : 'Rule',
            parent::buildClass($name)
        );
    }

    /**
     * Get the stub file for the generator.
	 * 获取生成器的存根文件
     *
     * @return string
     */
    protected function getStub()
    {
        $stub = '/stubs/rule.stub';

        if ($this->option('invokable')) {
            $stub = '/stubs/rule.invokable.stub';
        }

        if ($this->option('implicit') && $this->option('invokable')) {
            $stub = str_replace('.stub', '.implicit.stub', $stub);
        }

        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.$stub;
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
        return $rootNamespace.'\Rules';
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
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the rule already exists'],
            ['implicit', 'i', InputOption::VALUE_NONE, 'Generate an implicit rule'],
            ['invokable', null, InputOption::VALUE_NONE, 'Generate a single method, invokable rule class'],
        ];
    }
}
