<?php
/**
 * Illuminate，基础，测试，刷新数据库状态
 */

namespace Illuminate\Foundation\Testing;

class RefreshDatabaseState
{
    /**
     * Indicates if the test database has been migrated.
	 * 指示测试数据库是否已迁移
     *
     * @var bool
     */
    public static $migrated = false;

    /**
     * Indicates if a lazy refresh hook has been invoked.
	 * 指示是否调用了延迟刷新钩子
     *
     * @var bool
     */
    public static $lazilyRefreshed = false;
}
