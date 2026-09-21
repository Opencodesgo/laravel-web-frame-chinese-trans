<?php
/**
 * Illuminate，队列，无效负载异常
 */

namespace Illuminate\Queue;

use InvalidArgumentException;

class InvalidPayloadException extends InvalidArgumentException
{
    /**
     * The value that failed to decode.
	 * 解码失败的值
     *
     * @var mixed
     */
    public $value;

    /**
     * Create a new exception instance.
	 * 创建一个新的异常实例
     *
     * @param  string|null  $message
     * @param  mixed  $value
     * @return void
     */
    public function __construct($message = null, $value = null)
    {
        parent::__construct($message ?: json_last_error());

        $this->value = $value;
    }
}
