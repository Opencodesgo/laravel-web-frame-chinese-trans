<?php
/**
 * Illuminate，Redis，连接，Predis 集群连接
 */

namespace Illuminate\Redis\Connections;

use Predis\Command\ServerFlushDatabase;

class PredisClusterConnection extends PredisConnection
{
    /**
     * Flush the selected Redis database on all cluster nodes.
	 * 在所有集群节点上刷新所选Redis数据库
     *
     * @return void
     */
    public function flushdb()
    {
        $this->client->executeCommandOnNodes(
            tap(new ServerFlushDatabase)->setArguments(func_get_args())
        );
    }
}
