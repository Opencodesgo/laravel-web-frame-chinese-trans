<?php
/**
 * Illuminate，基础，控制台，make:observer 观察者制造命令
 */

namespace Illuminate\Foundation\Console;

use Illuminate\Console\GeneratorCommand;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'make:observer')]
class ObserverMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
	 * 控制台命令名称
     *
     * @var string
     */
    protected $name = 'make:observer';

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
    protected static $defaultName = 'make:observer';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Create a new observer class';

    /**
     * The type of class being generated.
	 * 生成的类的类型
     *
     * @var string
     */
    protected $type = 'Observer';

    /**
     * Build the class with the given name.
	 * 用给定的名称构建类
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $model = $this->option('model');

        return $model ? $this->replaceModel($stub, $model) : $stub;
    }

    /**
     * Replace the model for the given stub.
	 * 替换给定存根的模型
     *
     * @param  string  $stub
     * @param  string  $model
     * @return string
     */
    protected function replaceModel($stub, $model)
    {
        $modelClass = $this->parseModel($model);

        $replace = [
            'DummyFullModelClass' => $modelClass,
            '{{ namespacedModel }}' => $modelClass,
            '{{namespacedModel}}' => $modelClass,
            'DummyModelClass' => class_basename($modelClass),
            '{{ model }}' => class_basename($modelClass),
            '{{model}}' => class_basename($modelClass),
            'DummyModelVariable' => lcfirst(class_basename($modelClass)),
            '{{ modelVariable }}' => lcfirst(class_basename($modelClass)),
            '{{modelVariable}}' => lcfirst(class_basename($modelClass)),
        ];

        return str_replace(
            array_keys($replace), array_values($replace), $stub
        );
    }

    /**
     * Get the fully-qualified model class name.
	 * 获取完全限定的模型类名
     *
     * @param  string  $model
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function parseModel($model)
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        return $this->qualifyModel($model);
    }

    /**
     * Get the stub file for the generator.
	 * 获取生成器的存根文件
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->option('model')
            ? $this->resolveStubPath('/stubs/observer.stub')
            : $this->resolveStubPath('/stubs/observer.plain.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
	 * 解析到存根的全限定路
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
        return $rootNamespace.'\Observers';
    }

    /**
     * Get the console command arguments.
	 * 获取控制台命令参数
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the observer already exists'],
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'The model that the observer applies to'],
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

        $model = $this->components->askWithCompletion(
            'What model should this observer apply to?',
            $this->possibleModels(),
            'none'
        );

        if ($model && $model !== 'none') {
            $input->setOption('model', $model);
        }
    }
}
