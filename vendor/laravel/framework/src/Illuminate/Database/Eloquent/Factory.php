<?php
/**
 * Illuminate，数据库，Eloquent，工厂
 */

namespace Illuminate\Database\Eloquent;

use ArrayAccess;
use Faker\Generator as Faker;
use Symfony\Component\Finder\Finder;

class Factory implements ArrayAccess
{
    /**
     * The model definitions in the container.
	 * 容器中的模型定义
     *
     * @var array
     */
    protected $definitions = [];

    /**
     * The registered model states.
	 * 注册的模型状态
     *
     * @var array
     */
    protected $states = [];

    /**
     * The registered after making callbacks.
	 * 回调后注册
     *
     * @var array
     */
    protected $afterMaking = [];

    /**
     * The registered after creating callbacks.
	 * 创建回调后注册的
     *
     * @var array
     */
    protected $afterCreating = [];

    /**
     * The Faker instance for the builder.
	 * 建造者的Faker实例
     *
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * Create a new factory instance.
	 * 创建一个新的工厂实例
     *
     * @param  \Faker\Generator  $faker
     * @return void
     */
    public function __construct(Faker $faker)
    {
        $this->faker = $faker;
    }

    /**
     * Create a new factory container.
	 * 创建一个新的工厂容器
     *
     * @param  \Faker\Generator  $faker
     * @param  string|null  $pathToFactories
     * @return static
     */
    public static function construct(Faker $faker, $pathToFactories = null)
    {
        $pathToFactories = $pathToFactories ?: database_path('factories');

        return (new static($faker))->load($pathToFactories);
    }

    /**
     * Define a class with a given set of attributes.
	 * 定义一个具有给定属性集的类
     *
     * @param  string  $class
     * @param  callable  $attributes
     * @return $this
     */
    public function define($class, callable $attributes)
    {
        $this->definitions[$class] = $attributes;

        return $this;
    }

    /**
     * Define a state with a given set of attributes.
	 * 用给定的属性定义一个状态
     *
     * @param  string  $class
     * @param  string  $state
     * @param  callable|array  $attributes
     * @return $this
     */
    public function state($class, $state, $attributes)
    {
        $this->states[$class][$state] = $attributes;

        return $this;
    }

    /**
     * Define a callback to run after making a model.
	 * 在创建模型后定义回调
     *
     * @param  string  $class
     * @param  callable  $callback
     * @param  string  $name
     * @return $this
     */
    public function afterMaking($class, callable $callback, $name = 'default')
    {
        $this->afterMaking[$class][$name][] = $callback;

        return $this;
    }

    /**
     * Define a callback to run after making a model with given state.
	 * 在给定状态下创建模型后,定义一个回调
     *
     * @param  string  $class
     * @param  string  $state
     * @param  callable  $callback
     * @return $this
     */
    public function afterMakingState($class, $state, callable $callback)
    {
        return $this->afterMaking($class, $callback, $state);
    }

    /**
     * Define a callback to run after creating a model.
	 * 在创建模型后定义回调
     *
     * @param  string  $class
     * @param  callable  $callback
     * @param  string  $name
     * @return $this
     */
    public function afterCreating($class, callable $callback, $name = 'default')
    {
        $this->afterCreating[$class][$name][] = $callback;

        return $this;
    }

    /**
     * Define a callback to run after creating a model with given state.
	 * 定义一个回调，以便在创建具有给定状态的模型后运行。
     *
     * @param  string  $class
     * @param  string  $state
     * @param  callable  $callback
     * @return $this
     */
    public function afterCreatingState($class, $state, callable $callback)
    {
        return $this->afterCreating($class, $callback, $state);
    }

    /**
     * Create an instance of the given model and persist it to the database.
	 * 创建给定模型的实例并将其持久化到数据库中
     *
     * @param  string  $class
     * @param  array  $attributes
     * @return mixed
     */
    public function create($class, array $attributes = [])
    {
        return $this->of($class)->create($attributes);
    }

    /**
     * Create an instance of the given model.
	 * 创建给定模型的实例
     *
     * @param  string  $class
     * @param  array  $attributes
     * @return mixed
     */
    public function make($class, array $attributes = [])
    {
        return $this->of($class)->make($attributes);
    }

    /**
     * Get the raw attribute array for a given model.
	 * 获取给定模型的原始属性数组
     *
     * @param  string  $class
     * @param  array  $attributes
     * @return array
     */
    public function raw($class, array $attributes = [])
    {
        return array_merge(
            call_user_func($this->definitions[$class], $this->faker), $attributes
        );
    }

    /**
     * Create a builder for the given model.
	 * 为给定的模型创建一个构建器
     *
     * @param  string  $class
     * @return \Illuminate\Database\Eloquent\FactoryBuilder
     */
    public function of($class)
    {
        return new FactoryBuilder(
            $class, $this->definitions, $this->states,
            $this->afterMaking, $this->afterCreating, $this->faker
        );
    }

    /**
     * Load factories from path.
	 * 从路径加载工厂
     *
     * @param  string  $path
     * @return $this
     */
    public function load($path)
    {
        $factory = $this;

        if (is_dir($path)) {
            foreach (Finder::create()->files()->name('*.php')->in($path) as $file) {
                require $file->getRealPath();
            }
        }

        return $factory;
    }

    /**
     * Determine if the given offset exists.
	 * 确定给定的偏移量是否存在
     *
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->definitions[$offset]);
    }

    /**
     * Get the value of the given offset.
	 * 获取给定偏移量的值
     *
     * @param  string  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->make($offset);
    }

    /**
     * Set the given offset to the given value.
	 * 将给定偏移量设置为给定值
     *
     * @param  string  $offset
     * @param  callable  $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->define($offset, $value);
    }

    /**
     * Unset the value at the given offset.
	 * 在给定偏移量处取消值的设置
     *
     * @param  string  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->definitions[$offset]);
    }
}
