<?php
/**
 * Illuminate，基础，控制台，make:resource命令
 */

namespace Illuminate\Foundation\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:resource')]
class ResourceMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
	 * 控制台命令名称
     *
     * @var string
     */
    protected $name = 'make:resource';

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
    protected static $defaultName = 'make:resource';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Create a new resource';

    /**
     * The type of class being generated.
	 * 生成的类的类型
     *
     * @var string
     */
    protected $type = 'Resource';

    /**
     * Execute the console command.
	 * 执行console命令
     *
     * @return void
     */
    public function handle()
    {
        if ($this->collection()) {
            $this->type = 'Resource collection';
        }

        parent::handle();
    }

    /**
     * Get the stub file for the generator.
	 * 获取生成器的存根文件
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->collection()
                    ? $this->resolveStubPath('/stubs/resource-collection.stub')
                    : $this->resolveStubPath('/stubs/resource.stub');
    }

    /**
     * Determine if the command is generating a resource collection.
	 * 确定该命令是否正在生成资源集合
     *
     * @return bool
     */
    protected function collection()
    {
        return $this->option('collection') ||
               str_ends_with($this->argument('name'), 'Collection');
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
        return $rootNamespace.'\Http\Resources';
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
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the resource already exists'],
            ['collection', 'c', InputOption::VALUE_NONE, 'Create a resource collection'],
        ];
    }
}
