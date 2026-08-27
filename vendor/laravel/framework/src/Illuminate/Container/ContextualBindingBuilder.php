<?php
/**
 * Illuminate，容器，上下文绑定构建器
 */

namespace Illuminate\Container;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Container\ContextualBindingBuilder as ContextualBindingBuilderContract;

class ContextualBindingBuilder implements ContextualBindingBuilderContract
{
    /**
     * The underlying container instance.
	 * 底层容器实例
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * The concrete instance.
	 * 具体实例
     *
     * @var string|array
     */
    protected $concrete;

    /**
     * The abstract target.
	 * 抽象目标
     *
     * @var string
     */
    protected $needs;

    /**
     * Create a new contextual binding builder.
	 * 创建新的上下文绑定构建器
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @param  string|array  $concrete
     * @return void
     */
    public function __construct(Container $container, $concrete)
    {
        $this->concrete = $concrete;
        $this->container = $container;
    }

    /**
     * Define the abstract target that depends on the context.
	 * 定义依赖于上下文的抽象目标
     *
     * @param  string  $abstract
     * @return $this
     */
    public function needs($abstract)
    {
        $this->needs = $abstract;

        return $this;
    }

    /**
     * Define the implementation for the contextual binding.
	 * 定义上下文绑定的实现
     *
     * @param  \Closure|string|array  $implementation
     * @return void
     */
    public function give($implementation)
    {
        foreach (Util::arrayWrap($this->concrete) as $concrete) {
            $this->container->addContextualBinding($concrete, $this->needs, $implementation);
        }
    }

    /**
     * Define tagged services to be used as the implementation for the contextual binding.
	 * 定义标记的服务，用作上下文绑定的实现。
     *
     * @param  string  $tag
     * @return void
     */
    public function giveTagged($tag)
    {
        $this->give(function ($container) use ($tag) {
            $taggedServices = $container->tagged($tag);

            return is_array($taggedServices) ? $taggedServices : iterator_to_array($taggedServices);
        });
    }

    /**
     * Specify the configuration item to bind as a primitive.
	 * 指定要绑定的配置项作为原语
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return void
     */
    public function giveConfig($key, $default = null)
    {
        $this->give(fn ($container) => $container->get('config')->get($key, $default));
    }
}
