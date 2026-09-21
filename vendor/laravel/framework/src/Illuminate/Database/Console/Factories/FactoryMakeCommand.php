<?php
/**
 * Illuminate，数据库，控制台，工厂，工厂制造指令
 */

namespace Illuminate\Database\Console\Factories;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:factory')]
class FactoryMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
	 * 控制台命令名称
     *
     * @var string
     */
    protected $name = 'make:factory';

    /**
     * The name of the console command.
	 * console命令的名称
     *
     * This name is used to identify the command during lazy loading.
     *
     * @var string|null
     *
     * @deprecated
     */
    protected static $defaultName = 'make:factory';

    /**
     * The console command description.
	 * console命令说明
     *
     * @var string
     */
    protected $description = 'Create a new model factory';

    /**
     * The type of class being generated.
	 * 生成的类的类型
     *
     * @var string
     */
    protected $type = 'Factory';

    /**
     * Get the stub file for the generator.
	 * 获取生成器的存根文件
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/factory.stub');
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
     * Build the class with the given name.
	 * 用给定的名称构建类
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $factory = class_basename(Str::ucfirst(str_replace('Factory', '', $name)));

        $namespaceModel = $this->option('model')
                        ? $this->qualifyModel($this->option('model'))
                        : $this->qualifyModel($this->guessModelName($name));

        $model = class_basename($namespaceModel);

        $namespace = $this->getNamespace(
            Str::replaceFirst($this->rootNamespace(), 'Database\\Factories\\', $this->qualifyClass($this->getNameInput()))
        );

        $replace = [
            '{{ factoryNamespace }}' => $namespace,
            'NamespacedDummyModel' => $namespaceModel,
            '{{ namespacedModel }}' => $namespaceModel,
            '{{namespacedModel}}' => $namespaceModel,
            'DummyModel' => $model,
            '{{ model }}' => $model,
            '{{model}}' => $model,
            '{{ factory }}' => $factory,
            '{{factory}}' => $factory,
        ];

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
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
        $name = (string) Str::of($name)->replaceFirst($this->rootNamespace(), '')->finish('Factory');

        return $this->laravel->databasePath().'/factories/'.str_replace('\\', '/', $name).'.php';
    }

    /**
     * Guess the model name from the Factory name or return a default model name.
	 * 从Factory名称猜测模型名称，或者返回默认模型名称。
     *
     * @param  string  $name
     * @return string
     */
    protected function guessModelName($name)
    {
        if (str_ends_with($name, 'Factory')) {
            $name = substr($name, 0, -7);
        }

        $modelName = $this->qualifyModel(Str::after($name, $this->rootNamespace()));

        if (class_exists($modelName)) {
            return $modelName;
        }

        if (is_dir(app_path('Models/'))) {
            return $this->rootNamespace().'Models\Model';
        }

        return $this->rootNamespace().'Model';
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
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'The name of the model'],
        ];
    }
}
