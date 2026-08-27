<?php
/**
 * Illuminate，Redis，连接，Predis 连接器
 */

namespace Illuminate\Redis\Connections;

use Closure;
use Illuminate\Contracts\Redis\Connection as ConnectionContract;
use Predis\Command\Argument\ArrayableArgument;

/**
 * @mixin \Predis\Client
 */
class PredisConnection extends Connection implements ConnectionContract
{
    /**
     * The Predis client.
	 * Predis客户端
     *
     * @var \Predis\Client
     */
    protected $client;

    /**
     * Create a new Predis connection.
	 * 创建新的Predis连接
     *
     * @param  \Predis\Client  $client
     * @return void
     */
    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * Subscribe to a set of given channels for messages.
	 * 为消息订阅一组给定的通道
     *
     * @param  array|string  $channels
     * @param  \Closure  $callback
     * @param  string  $method
     * @return void
     */
    public function createSubscription($channels, Closure $callback, $method = 'subscribe')
    {
        $loop = $this->pubSubLoop();

        $loop->{$method}(...array_values((array) $channels));

        foreach ($loop as $message) {
            if ($message->kind === 'message' || $message->kind === 'pmessage') {
                $callback($message->payload, $message->channel);
            }
        }

        unset($loop);
    }

    /**
     * Parse the command's parameters for event dispatching.
	 * 解析命令的参数以进行事件调度
     *
     * @param  array  $parameters
     * @return array
     */
    protected function parseParametersForEvent(array $parameters)
    {
        return collect($parameters)
            ->transform(function ($parameter) {
                return $parameter instanceof ArrayableArgument
                    ? $parameter->toArray()
                    : $parameter;
            })->all();
    }
}
