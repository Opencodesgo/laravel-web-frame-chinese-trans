<?php
/**
 * Illuminate，视图，组件
 */

namespace Illuminate\View;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View as ViewContract;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

abstract class Component
{
    /**
     * The properties / methods that should not be exposed to the component.
	 * 不应该向组件公开的属性/方法
     *
     * @var array
     */
    protected $except = [];

    /**
     * The component alias name.
	 * 组件别名
     *
     * @var string
     */
    public $componentName;

    /**
     * The component attributes.
	 * 组件属性
     *
     * @var \Illuminate\View\ComponentAttributeBag
     */
    public $attributes;

    /**
     * The view factory instance, if any.
	 * 视图工厂实例（如果有）
     *
     * @var \Illuminate\Contracts\View\Factory|null
     */
    protected static $factory;

    /**
     * The component resolver callback.
	 * 组件解析器回调
     *
     * @var (\Closure(string, array): Component)|null
     */
    protected static $componentsResolver;

    /**
     * The cache of blade view names, keyed by contents.
	 * 刀片视图名称的缓存，按内容进行键控。
     *
     * @var array<string, string>
     */
    protected static $bladeViewCache = [];

    /**
     * The cache of public property names, keyed by class.
	 * 公共属性名称的缓存，按类进行键控。
     *
     * @var array
     */
    protected static $propertyCache = [];

    /**
     * The cache of public method names, keyed by class.
	 * 公共方法名的缓存，按类键。
     *
     * @var array
     */
    protected static $methodCache = [];

    /**
     * The cache of constructor parameters, keyed by class.
	 * 构造函数参数的缓存，按类键。
     *
     * @var array<class-string, array<int, string>>
     */
    protected static $constructorParametersCache = [];

    /**
     * Get the view / view contents that represent the component.
	 * 获取表示组件的视图/视图内容
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\Support\Htmlable|\Closure|string
     */
    abstract public function render();

    /**
     * Resolve the component instance with the given data.
	 * 使用给定的数据解析组件实例
     *
     * @param  array  $data
     * @return static
     */
    public static function resolve($data)
    {
        if (static::$componentsResolver) {
            return call_user_func(static::$componentsResolver, static::class, $data);
        }

        $parameters = static::extractConstructorParameters();

        $dataKeys = array_keys($data);

        if (empty(array_diff($parameters, $dataKeys))) {
            return new static(...array_intersect_key($data, array_flip($parameters)));
        }

        return Container::getInstance()->make(static::class, $data);
    }

    /**
     * Extract the constructor parameters for the component.
	 * 提取组件的构造函数参数
     *
     * @return array
     */
    protected static function extractConstructorParameters()
    {
        if (! isset(static::$constructorParametersCache[static::class])) {
            $class = new ReflectionClass(static::class);

            $constructor = $class->getConstructor();

            static::$constructorParametersCache[static::class] = $constructor
                ? collect($constructor->getParameters())->map->getName()->all()
                : [];
        }

        return static::$constructorParametersCache[static::class];
    }

    /**
     * Resolve the Blade view or view file that should be used when rendering the component.
	 * 解析呈现组件时应该使用的Blade视图或视图文件
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\Support\Htmlable|\Closure|string
     */
    public function resolveView()
    {
        $view = $this->render();

        if ($view instanceof ViewContract) {
            return $view;
        }

        if ($view instanceof Htmlable) {
            return $view;
        }

        $resolver = function ($view) {
            return $this->extractBladeViewFromString($view);
        };

        return $view instanceof Closure ? function (array $data = []) use ($view, $resolver) {
            return $resolver($view($data));
        }
        : $resolver($view);
    }

    /**
     * Create a Blade view with the raw component string content.
	 * 创建一个带有原始组件字符串内容的Blade视图
     *
     * @param  string  $contents
     * @return string
     */
    protected function extractBladeViewFromString($contents)
    {
        $key = sprintf('%s::%s', static::class, $contents);

        if (isset(static::$bladeViewCache[$key])) {
            return static::$bladeViewCache[$key];
        }

        if (strlen($contents) <= PHP_MAXPATHLEN && $this->factory()->exists($contents)) {
            return static::$bladeViewCache[$key] = $contents;
        }

        return static::$bladeViewCache[$key] = $this->createBladeViewFromString($this->factory(), $contents);
    }

    /**
     * Create a Blade view with the raw component string content.
	 * 创建一个带有原始组件字符串内容的Blade视图
     *
     * @param  \Illuminate\Contracts\View\Factory  $factory
     * @param  string  $contents
     * @return string
     */
    protected function createBladeViewFromString($factory, $contents)
    {
        $factory->addNamespace(
            '__components',
            $directory = Container::getInstance()['config']->get('view.compiled')
        );

        if (! is_file($viewFile = $directory.'/'.sha1($contents).'.blade.php')) {
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents($viewFile, $contents);
        }

        return '__components::'.basename($viewFile, '.blade.php');
    }

    /**
     * Get the data that should be supplied to the view.
	 * 获取应该提供给视图的数据
     *
     * @author Freek Van der Herten
     * @author Brent Roose
     *
     * @return array
     */
    public function data()
    {
        $this->attributes = $this->attributes ?: $this->newAttributeBag();

        return array_merge($this->extractPublicProperties(), $this->extractPublicMethods());
    }

    /**
     * Extract the public properties for the component.
	 * 提取组件的公共属性
     *
     * @return array
     */
    protected function extractPublicProperties()
    {
        $class = get_class($this);

        if (! isset(static::$propertyCache[$class])) {
            $reflection = new ReflectionClass($this);

            static::$propertyCache[$class] = collect($reflection->getProperties(ReflectionProperty::IS_PUBLIC))
                ->reject(function (ReflectionProperty $property) {
                    return $property->isStatic();
                })
                ->reject(function (ReflectionProperty $property) {
                    return $this->shouldIgnore($property->getName());
                })
                ->map(function (ReflectionProperty $property) {
                    return $property->getName();
                })->all();
        }

        $values = [];

        foreach (static::$propertyCache[$class] as $property) {
            $values[$property] = $this->{$property};
        }

        return $values;
    }

    /**
     * Extract the public methods for the component.
	 * 提取组件的公共方法
     *
     * @return array
     */
    protected function extractPublicMethods()
    {
        $class = get_class($this);

        if (! isset(static::$methodCache[$class])) {
            $reflection = new ReflectionClass($this);

            static::$methodCache[$class] = collect($reflection->getMethods(ReflectionMethod::IS_PUBLIC))
                ->reject(function (ReflectionMethod $method) {
                    return $this->shouldIgnore($method->getName());
                })
                ->map(function (ReflectionMethod $method) {
                    return $method->getName();
                });
        }

        $values = [];

        foreach (static::$methodCache[$class] as $method) {
            $values[$method] = $this->createVariableFromMethod(new ReflectionMethod($this, $method));
        }

        return $values;
    }

    /**
     * Create a callable variable from the given method.
	 * 从给定的方法创建一个可调用的变量
     *
     * @param  \ReflectionMethod  $method
     * @return mixed
     */
    protected function createVariableFromMethod(ReflectionMethod $method)
    {
        return $method->getNumberOfParameters() === 0
                        ? $this->createInvokableVariable($method->getName())
                        : Closure::fromCallable([$this, $method->getName()]);
    }

    /**
     * Create an invokable, toStringable variable for the given component method.
	 * 为给定的组件方法创建一个可调用的toStringable变量
     *
     * @param  string  $method
     * @return \Illuminate\View\InvokableComponentVariable
     */
    protected function createInvokableVariable(string $method)
    {
        return new InvokableComponentVariable(function () use ($method) {
            return $this->{$method}();
        });
    }

    /**
     * Determine if the given property / method should be ignored.
	 * 确定是否应该忽略给定的属性/方法
     *
     * @param  string  $name
     * @return bool
     */
    protected function shouldIgnore($name)
    {
        return str_starts_with($name, '__') ||
               in_array($name, $this->ignoredMethods());
    }

    /**
     * Get the methods that should be ignored.
	 * 获取应该忽略的方法
     *
     * @return array
     */
    protected function ignoredMethods()
    {
        return array_merge([
            'data',
            'render',
            'resolveView',
            'shouldRender',
            'view',
            'withName',
            'withAttributes',
        ], $this->except);
    }

    /**
     * Set the component alias name.
	 * 设置组件别名
     *
     * @param  string  $name
     * @return $this
     */
    public function withName($name)
    {
        $this->componentName = $name;

        return $this;
    }

    /**
     * Set the extra attributes that the component should make available.
	 * 设置组件应该提供的额外属性
     *
     * @param  array  $attributes
     * @return $this
     */
    public function withAttributes(array $attributes)
    {
        $this->attributes = $this->attributes ?: $this->newAttributeBag();

        $this->attributes->setAttributes($attributes);

        return $this;
    }

    /**
     * Get a new attribute bag instance.
	 * 获取一个新的属性包实例
     *
     * @param  array  $attributes
     * @return \Illuminate\View\ComponentAttributeBag
     */
    protected function newAttributeBag(array $attributes = [])
    {
        return new ComponentAttributeBag($attributes);
    }

    /**
     * Determine if the component should be rendered.
	 * 确定是否应该呈现组件
     *
     * @return bool
     */
    public function shouldRender()
    {
        return true;
    }

    /**
     * Get the evaluated view contents for the given view.
	 * 获取给定视图的求值视图内容
     *
     * @param  string|null  $view
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $data
     * @param  array  $mergeData
     * @return \Illuminate\Contracts\View\View
     */
    public function view($view, $data = [], $mergeData = [])
    {
        return $this->factory()->make($view, $data, $mergeData);
    }

    /**
     * Get the view factory instance.
	 * 获取视图工厂实例
     *
     * @return \Illuminate\Contracts\View\Factory
     */
    protected function factory()
    {
        if (is_null(static::$factory)) {
            static::$factory = Container::getInstance()->make('view');
        }

        return static::$factory;
    }

    /**
     * Flush the component's cached state.
	 * 刷新组件的缓存状态
     *
     * @return void
     */
    public static function flushCache()
    {
        static::$bladeViewCache = [];
        static::$constructorParametersCache = [];
        static::$methodCache = [];
        static::$propertyCache = [];
    }

    /**
     * Forget the component's factory instance.
	 * 忘记组件的工厂实例吧
     *
     * @return void
     */
    public static function forgetFactory()
    {
        static::$factory = null;
    }

    /**
     * Forget the component's resolver callback.
	 * 忘记组件的解析器回调吧
     *
     * @return void
     *
     * @internal
     */
    public static function forgetComponentsResolver()
    {
        static::$componentsResolver = null;
    }

    /**
     * Set the callback that should be used to resolve components within views.
	 * 设置应该用于解析视图内组件的回调
     *
     * @param  \Closure(string $component, array $data): Component  $resolver
     * @return void
     *
     * @internal
     */
    public static function resolveComponentsUsing($resolver)
    {
        static::$componentsResolver = $resolver;
    }
}
