<?php
/**
 * Illuminate，基础，控制台，make:event 命令
 */

namespace Illuminate\Foundation\Console;

use Illuminate\Console\GeneratorCommand;

class EventMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
	 * 控制台命令名称 make:event
     *
     * @var string
     */
    protected $name = 'make:event';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Create a new event class';

    /**
     * The type of class being generated.
	 * 生成器类类型
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
	 * 得到生成器存根文件
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
	 * 得到类的默认命名空间
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Events';
    }
}
