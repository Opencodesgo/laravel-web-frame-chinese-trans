<?php
/**
 * Illuminate，基础，控制台，make:cast 命令
 */

namespace Illuminate\Foundation\Console;

use Illuminate\Console\GeneratorCommand;

class CastMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
	 * 控制台命令名 make:cast
     *
     * @var string
     */
    protected $name = 'make:cast';

    /**
     * The console command description.
	 * 控制台命令描述，创建一个新的自定义Eloquent转换类
     *
     * @var string
     */
    protected $description = 'Create a new custom Eloquent cast class';

    /**
     * The type of class being generated.
	 * 生成的类类型
     *
     * @var string
     */
    protected $type = 'Cast';

    /**
     * Get the stub file for the generator.
	 * 得到生成的存根文件
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/cast.stub');
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
        return $rootNamespace.'\Casts';
    }
}
