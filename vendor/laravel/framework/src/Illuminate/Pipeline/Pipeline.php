<?php
/**
 * Illuminate，分页，管道
 */

namespace Illuminate\Pipeline;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Pipeline\Pipeline as PipelineContract;
use RuntimeException;
use Throwable;

class Pipeline implements PipelineContract
{
    /**
     * The container implementation.
	 * 容器实现
     *
     * @var \Illuminate\Contracts\Container\Container|null
     */
    protected $container;

    /**
     * The object being passed through the pipeline.
	 * 正在通过管道传递的对象
     *
     * @var mixed
     */
    protected $passable;

    /**
     * The array of class pipes.
	 * 类管道数组
     *
     * @var array
     */
    protected $pipes = [];

    /**
     * The method to call on each pipe.
	 * 要在每个管道上调用的方法
     *
     * @var string
     */
    protected $method = 'handle';

    /**
     * Create a new class instance.
	 * 创建一个新的类实例
     *
     * @param  \Illuminate\Contracts\Container\Container|null  $container
     * @return void
     */
    public function __construct(Container $container = null)
    {
        $this->container = $container;
    }

    /**
     * Set the object being sent through the pipeline.
	 * 设置通过管道发送的对象
     *
     * @param  mixed  $passable
     * @return $this
     */
    public function send($passable)
    {
        $this->passable = $passable;

        return $this;
    }

    /**
     * Set the array of pipes.
	 * 设置管道数组
     *
     * @param  array|mixed  $pipes
     * @return $this
     */
    public function through($pipes)
    {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();

        return $this;
    }

    /**
     * Push additional pipes onto the pipeline.
	 * 将额外的管道推到管道上
     *
     * @param  array|mixed  $pipes
     * @return $this
     */
    public function pipe($pipes)
    {
        array_push($this->pipes, ...(is_array($pipes) ? $pipes : func_get_args()));

        return $this;
    }

    /**
     * Set the method to call on the pipes.
	 * 设置要在管道上调用的方法
     *
     * @param  string  $method
     * @return $this
     */
    public function via($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Run the pipeline with a final destination callback.
	 * 运行带有最终目的地回调的管道
     *
     * @param  \Closure  $destination
     * @return mixed
     */
    public function then(Closure $destination)
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes()), $this->carry(), $this->prepareDestination($destination)
        );

        return $pipeline($this->passable);
    }

    /**
     * Run the pipeline and return the result.
	 * 运行管道并返回结果
     *
     * @return mixed
     */
    public function thenReturn()
    {
        return $this->then(function ($passable) {
            return $passable;
        });
    }

    /**
     * Get the final piece of the Closure onion.
	 * 获取Closure洋葱的最后一部分
     *
     * @param  \Closure  $destination
     * @return \Closure
     */
    protected function prepareDestination(Closure $destination)
    {
        return function ($passable) use ($destination) {
            try {
                return $destination($passable);
            } catch (Throwable $e) {
                return $this->handleException($passable, $e);
            }
        };
    }

    /**
     * Get a Closure that represents a slice of the application onion.
	 * 获取一个表示应用程序洋葱部分的Closure
     *
     * @return \Closure
     */
    protected function carry()
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                try {
                    if (is_callable($pipe)) {
                        // If the pipe is a callable, then we will call it directly, but otherwise we
                        // will resolve the pipes out of the dependency container and call it with
                        // the appropriate method and arguments, returning the results back out.
						// 如果管道是可调用的，那么我们将直接调用它。
                        return $pipe($passable, $stack);
                    } elseif (! is_object($pipe)) {
                        [$name, $parameters] = $this->parsePipeString($pipe);

                        // If the pipe is a string we will parse the string and resolve the class out
                        // of the dependency injection container. We can then build a callable and
                        // execute the pipe function giving in the parameters that are required.
						// 如果管道是字符串，我们将解析该字符串。
                        $pipe = $this->getContainer()->make($name);

                        $parameters = array_merge([$passable, $stack], $parameters);
                    } else {
                        // If the pipe is already an object we'll just make a callable and pass it to
                        // the pipe as-is. There is no need to do any extra parsing and formatting
                        // since the object we're given was already a fully instantiated object.
						// 如果管道已经是一个对象，我们就创建一个可调用对象。
                        $parameters = [$passable, $stack];
                    }

                    $carry = method_exists($pipe, $this->method)
                                    ? $pipe->{$this->method}(...$parameters)
                                    : $pipe(...$parameters);

                    return $this->handleCarry($carry);
                } catch (Throwable $e) {
                    return $this->handleException($passable, $e);
                }
            };
        };
    }

    /**
     * Parse full pipe string to get name and parameters.
	 * 解析完整的管道字符串以获取名称和参数
     *
     * @param  string  $pipe
     * @return array
     */
    protected function parsePipeString($pipe)
    {
        [$name, $parameters] = array_pad(explode(':', $pipe, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return [$name, $parameters];
    }

    /**
     * Get the array of configured pipes.
	 * 获取已配置管道的数组
     *
     * @return array
     */
    protected function pipes()
    {
        return $this->pipes;
    }

    /**
     * Get the container instance.
	 * 得到容器实例
     *
     * @return \Illuminate\Contracts\Container\Container
     *
     * @throws \RuntimeException
     */
    protected function getContainer()
    {
        if (! $this->container) {
            throw new RuntimeException('A container instance has not been passed to the Pipeline.');
        }

        return $this->container;
    }

    /**
     * Set the container instance.
	 * 设置容器实例
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Handle the value returned from each pipe before passing it to the next.
	 * 在将每个管道返回的值传递给下一个管道之前处理它
     *
     * @param  mixed  $carry
     * @return mixed
     */
    protected function handleCarry($carry)
    {
        return $carry;
    }

    /**
     * Handle the given exception.
	 * 处理给定的异常
     *
     * @param  mixed  $passable
     * @param  \Throwable  $e
     * @return mixed
     *
     * @throws \Throwable
     */
    protected function handleException($passable, Throwable $e)
    {
        throw $e;
    }
}
