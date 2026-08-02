<?php
/**
 * Illuminate，基础，测试，刷新数据库状态
 */

namespace Illuminate\Foundation\Testing;

class RefreshDatabaseState
{
    /**
     * Indicates if the test database has been migrated.
	 * 指示是否迁移了测试数据库
     *
     * @var bool
     */
    public static $migrated = false;
}
