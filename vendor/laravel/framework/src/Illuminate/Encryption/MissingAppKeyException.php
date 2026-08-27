<?php
/**
 * Illuminate，加密，App Key丢失异常
 */

namespace Illuminate\Encryption;

use RuntimeException;

class MissingAppKeyException extends RuntimeException
{
    /**
     * Create a new exception instance.
	 * 创建新的异常实例
     *
     * @param  string  $message
     * @return void
     */
    public function __construct($message = 'No application encryption key has been specified.')
    {
        parent::__construct($message);
    }
}
