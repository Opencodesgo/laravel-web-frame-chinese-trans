<?php
/**
 * Illuminate，基础，测试，问题，与弃用处理交互
 */

namespace Illuminate\Foundation\Testing\Concerns;

use ErrorException;

trait InteractsWithDeprecationHandling
{
    /**
     * The original deprecation handler.
	 * 原始弃用处理程序
     *
     * @var callable|null
     */
    protected $originalDeprecationHandler;

    /**
     * Restore deprecation handling.
	 * 恢复弃用处理
     *
     * @return $this
     */
    protected function withDeprecationHandling()
    {
        if ($this->originalDeprecationHandler) {
            set_error_handler(tap($this->originalDeprecationHandler, function () {
                $this->originalDeprecationHandler = null;
            }));
        }

        return $this;
    }

    /**
     * Disable deprecation handling for the test.
	 * 禁用测试的弃用处理
     *
     * @return $this
     */
    protected function withoutDeprecationHandling()
    {
        if ($this->originalDeprecationHandler == null) {
            $this->originalDeprecationHandler = set_error_handler(function ($level, $message, $file = '', $line = 0) {
                if (in_array($level, [E_DEPRECATED, E_USER_DEPRECATED]) || (error_reporting() & $level)) {
                    throw new ErrorException($message, 0, $level, $file, $line);
                }
            });
        }

        return $this;
    }
}
