<?php
/**
 * Illuminate，契约，Redis，连接器
 */

namespace Illuminate\Contracts\Redis;

interface Connector
{
    /**
     * Create a connection to a Redis cluster.
	 * 创建到Redis集群的连接
     *
     * @param  array  $config
     * @param  array  $options
     * @return \Illuminate\Redis\Connections\Connection
     */
    public function connect(array $config, array $options);

    /**
     * Create a connection to a Redis instance.
	 * 创建一个到Redis实例的连接
     *
     * @param  array  $config
     * @param  array  $clusterOptions
     * @param  array  $options
     * @return \Illuminate\Redis\Connections\Connection
     */
    public function connectToCluster(array $config, array $clusterOptions, array $options);
}
