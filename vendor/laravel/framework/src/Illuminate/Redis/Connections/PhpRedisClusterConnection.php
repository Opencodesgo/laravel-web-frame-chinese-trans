<?php
/**
 * Illuminate，Redis，连接，Php Redis 集群连接
 */

namespace Illuminate\Redis\Connections;

class PhpRedisClusterConnection extends PhpRedisConnection
{
    /**
     * Flush the selected Redis database on all master nodes.
	 * 在所有主节点上刷新所选Redis数据库
     *
     * @return mixed
     */
    public function flushdb()
    {
        $arguments = func_get_args();

        $async = strtoupper((string) ($arguments[0] ?? null)) === 'ASYNC';

        foreach ($this->client->_masters() as $master) {
            $async
                ? $this->command('rawCommand', [$master, 'flushdb', 'async'])
                : $this->command('flushdb', [$master]);
        }
    }
}
