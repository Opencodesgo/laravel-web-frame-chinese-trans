<?php
/**
 * Illuminate，路由，异常，备份Enum大小写未发现异常
 */

namespace Illuminate\Routing\Exceptions;

use RuntimeException;

class BackedEnumCaseNotFoundException extends RuntimeException
{
    /**
     * Create a new exception instance.
	 * 创建新的异常实例
     *
     * @param  string  $backedEnumClass
     * @param  string  $case
     * @return void
     */
    public function __construct($backedEnumClass, $case)
    {
        parent::__construct("Case [{$case}] not found on Backed Enum [{$backedEnumClass}].");
    }
}
