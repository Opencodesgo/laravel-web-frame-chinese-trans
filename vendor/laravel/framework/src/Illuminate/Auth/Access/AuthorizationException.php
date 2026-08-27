<?php
/**
 * Illuminate，认证，访问，授权异常
 */

namespace Illuminate\Auth\Access;

use Exception;
use Throwable;

class AuthorizationException extends Exception
{
    /**
     * The response from the gate.
	 * 大门响应
     *
     * @var \Illuminate\Auth\Access\Response
     */
    protected $response;

    /**
     * The HTTP response status code.
	 * HTTP响应状态码
     *
     * @var int|null
     */
    protected $status;

    /**
     * Create a new authorization exception instance.
	 * 创建新的授权异常实例
     *
     * @param  string|null  $message
     * @param  mixed  $code
     * @param  \Throwable|null  $previous
     * @return void
     */
    public function __construct($message = null, $code = null, Throwable $previous = null)
    {
        parent::__construct($message ?? 'This action is unauthorized.', 0, $previous);

        $this->code = $code ?: 0;
    }

    /**
     * Get the response from the gate.
	 * 得到大门响应
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * Set the response from the gate.
	 * 设置大门响应
     *
     * @param  \Illuminate\Auth\Access\Response  $response
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Set the HTTP response status code.
	 * 设置HTTP响应状态码
     *
     * @param  int|null  $status
     * @return $this
     */
    public function withStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Set the HTTP response status code to 404.
	 * 设置HTTP响应状态码为404
     *
     * @return $this
     */
    public function asNotFound()
    {
        return $this->withStatus(404);
    }

    /**
     * Determine if the HTTP status code has been set.
	 * 确定是否设置了HTTP状态码
     *
     * @return bool
     */
    public function hasStatus()
    {
        return $this->status !== null;
    }

    /**
     * Get the HTTP status code.
	 * 获取HTTP状态码
     *
     * @return int|null
     */
    public function status()
    {
        return $this->status;
    }

    /**
     * Create a deny response object from this exception.
	 * 创建一个拒绝响应对象从此异常
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function toResponse()
    {
        return Response::deny($this->message, $this->code)->withStatus($this->status);
    }
}
