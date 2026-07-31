<?php
/**
 * Illuminate，Http，客户端，集中资源
 */

namespace Illuminate\Http\Client;

use GuzzleHttp\Utils;

/**
 * @mixin \Illuminate\Http\Client\Factory
 */
class Pool
{
    /**
     * The factory instance.
	 * 工厂实例
     *
     * @var \Illuminate\Http\Client\Factory
     */
    protected $factory;

    /**
     * The handler function for the Guzzle client.
	 * Guzzle客户端的处理程序函数
     *
     * @var callable
     */
    protected $handler;

    /**
     * The pool of requests.
	 * 请求池
     *
     * @var array
     */
    protected $pool = [];

    /**
     * Create a new requests pool.
	 * 创建一个新的请求池
     *
     * @param  \Illuminate\Http\Client\Factory|null  $factory
     * @return void
     */
    public function __construct(Factory $factory = null)
    {
        $this->factory = $factory ?: new Factory();

        if (method_exists(Utils::class, 'chooseHandler')) {
            $this->handler = Utils::chooseHandler();
        } else {
            $this->handler = \GuzzleHttp\choose_handler();
        }
    }

    /**
     * Add a request to the pool with a key.
	 * 使用密钥向池中添加请求
     *
     * @param  string  $key
     * @return \Illuminate\Http\Client\PendingRequest
     */
    public function as(string $key)
    {
        return $this->pool[$key] = $this->asyncRequest();
    }

    /**
     * Retrieve a new async pending request.
	 * 检索新的异步挂起请求
     *
     * @return \Illuminate\Http\Client\PendingRequest
     */
    protected function asyncRequest()
    {
        return $this->factory->setHandler($this->handler)->async();
    }

    /**
     * Retrieve the requests in the pool.
	 * 检索池中的请求
     *
     * @return array
     */
    public function getRequests()
    {
        return $this->pool;
    }

    /**
     * Add a request to the pool with a numeric index.
	 * 向池中添加带有数字索引的请求
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return \Illuminate\Http\Client\PendingRequest
     */
    public function __call($method, $parameters)
    {
        return $this->pool[] = $this->asyncRequest()->$method(...$parameters);
    }
}
