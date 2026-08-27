<?php
/**
 * Illuminate，数据库，问题，构建查询
 */

namespace Illuminate\Database\Concerns;

use Illuminate\Support\Collection;

trait ExplainsQueries
{
    /**
     * Explains the query.
	 * 解析查询语句
     *
     * @return \Illuminate\Support\Collection
     */
    public function explain()
    {
        $sql = $this->toSql();

        $bindings = $this->getBindings();

        $explanation = $this->getConnection()->select('EXPLAIN '.$sql, $bindings);

        return new Collection($explanation);
    }
}
