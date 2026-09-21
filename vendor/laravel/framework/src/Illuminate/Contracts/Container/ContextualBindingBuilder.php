<?php
/**
 * Illuminate，契约，容器，上下文绑定构建器
 */

namespace Illuminate\Contracts\Container;

interface ContextualBindingBuilder
{
    /**
     * Define the abstract target that depends on the context.
	 * 定义依赖于上下文的抽象对象
     *
     * @param  string  $abstract
     * @return $this
     */
    public function needs($abstract);

    /**
     * Define the implementation for the contextual binding.
	 * 定义上下文绑定的实现
     *
     * @param  \Closure|string|array  $implementation
     * @return void
     */
    public function give($implementation);

    /**
     * Define tagged services to be used as the implementation for the contextual binding.
	 * 定义标记的服务，用作上下文绑定的实现。
     *
     * @param  string  $tag
     * @return void
     */
    public function giveTagged($tag);

    /**
     * Specify the configuration item to bind as a primitive.
	 * 指定要绑定的配置项作为原语
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return void
     */
    public function giveConfig($key, $default = null);
}
