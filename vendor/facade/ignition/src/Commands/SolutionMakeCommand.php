<?php
/**
 * Facade，Ignition，命令，解决方案生成指令
 */

namespace Facade\Ignition\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class SolutionMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
	 * 控制台命令名
     *
     * @var string
     */
    protected $name = 'ignition:make-solution';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Create a new custom Ignition solution class';

    /**
     * The type of class being generated.
	 * 生成的类类型
     *
     * @var string
     */
    protected $type = 'Solution';

    /**
     * Get the stub file for the generator.
	 * 获取生成器的存根文件
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->option('runnable')
            ? __DIR__.'/stubs/runnable-solution.stub'
            : __DIR__.'/stubs/solution.stub';
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
        return $rootNamespace.'\Solutions';
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
            ['runnable', null, InputOption::VALUE_NONE, 'Create runnable solution'],
        ];
    }
}
