<?php
/**
 * Facade，Ignition，命令，解决方案提供程序
 */

namespace Facade\Ignition\Commands;

use Illuminate\Console\GeneratorCommand;

class SolutionProviderMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
	 * 控制台命令名
     *
     * @var string
     */
    protected $name = 'ignition:make-solution-provider';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Create a new custom Ignition solution provider class';

    /**
     * The type of class being generated.
	 * 生成的类类型
     *
     * @var string
     */
    protected $type = 'Solution Provider';

    /**
     * Get the stub file for the generator.
	 * 获取生成器的存根文件
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/solution-provider.stub';
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
        return $rootNamespace.'\SolutionProviders';
    }
}
