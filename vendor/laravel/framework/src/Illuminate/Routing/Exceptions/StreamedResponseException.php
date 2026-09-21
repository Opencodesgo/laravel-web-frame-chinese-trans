<?php
/**
 * Illuminate，路由，异常，流响应异常
 */

namespace Illuminate\Routing\Exceptions;

use Illuminate\Http\Response;
use RuntimeException;
use Throwable;

class StreamedResponseException extends RuntimeException
{
    /**
     * The actual exception thrown during the stream.
	 * 流期间抛出的实际异常
     *
     * @var \Throwable
     */
    public $originalException;

    /**
     * Create a new exception instance.
	 * 创建一个新的异常实例
     *
     * @param  \Throwable  $originalException
     * @return void
     */
    public function __construct(Throwable $originalException)
    {
        $this->originalException = $originalException;

        parent::__construct($originalException->getMessage());
    }

    /**
     * Render the exception.
	 * 呈现异常
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return new Response('');
    }

    /**
     * Get the actual exception thrown during the stream.
	 * 获取流期间抛出的实际异常
     *
     * @return \Throwable
     */
    public function getInnerException()
    {
        return $this->originalException;
    }
}
