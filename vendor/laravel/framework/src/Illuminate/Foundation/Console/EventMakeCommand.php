<?php
/**
 * Illuminate，基础，控制台，make:event 事件生成命令
 */

namespace Illuminate\Foundation\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:event')]
class EventMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
	 * 控制台命令名
     *
     * @var string
     */
    protected $name = 'make:event';

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
    protected static $defaultName = 'make:event';

    /**
     * The console command description.
	 * 控制台命令说明
     *
     * @var string
     */
    protected $description = 'Create a new event class';

    /**
     * The type of class being generated.
	 * 生成的类的类型
     *
     * @var string
     */
    protected $type = 'Event';

    /**
     * Determine if the class already exists.
	 * 确定类是否已经存在
     *
     * @param  string  $rawName
     * @return bool
     */
    protected function alreadyExists($rawName)
    {
        return class_exists($rawName) ||
               $this->files->exists($this->getPath($this->qualifyClass($rawName)));
    }

    /**
     * Get the stub file for the generator.
	 * 获取生成器的存根文件
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/event.stub');
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
     * Get the default namespace for the class.
	 * 获取类的默认名称空间
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Events';
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
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the event already exists'],
        ];
    }
}
