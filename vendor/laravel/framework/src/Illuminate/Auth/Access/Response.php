<?php
/**
 * Illuminate，认证，访问，响应
 */

namespace Illuminate\Auth\Access;

use Illuminate\Contracts\Support\Arrayable;

class Response implements Arrayable
{
    /**
     * Indicates whether the response was allowed.
	 * 指明是否允许响应
     *
     * @var bool
     */
    protected $allowed;

    /**
     * The response message.
	 * 响应消息
     *
     * @var string|null
     */
    protected $message;

    /**
     * The response code.
	 * 响应代码
     *
     * @var mixed
     */
    protected $code;

    /**
     * The HTTP response status code.
	 * HTTP响应状态码
     *
     * @var int|null
     */
    protected $status;

    /**
     * Create a new response.
	 * 创建新的响应
     *
     * @param  bool  $allowed
     * @param  string  $message
     * @param  mixed  $code
     * @return void
     */
    public function __construct($allowed, $message = '', $code = null)
    {
        $this->code = $code;
        $this->allowed = $allowed;
        $this->message = $message;
    }

    /**
     * Create a new "allow" Response.
	 * 创建新的"allow"响应
     *
     * @param  string|null  $message
     * @param  mixed  $code
     * @return \Illuminate\Auth\Access\Response
     */
    public static function allow($message = null, $code = null)
    {
        return new static(true, $message, $code);
    }

    /**
     * Create a new "deny" Response.
	 * 创建新的"deny"响应
     *
     * @param  string|null  $message
     * @param  mixed  $code
     * @return \Illuminate\Auth\Access\Response
     */
    public static function deny($message = null, $code = null)
    {
        return new static(false, $message, $code);
    }

    /**
     * Create a new "deny" Response with a HTTP status code.
	 * 用HTTP状态码创建一个新的"deny"响应
     *
     * @param  int  $status
     * @param  string|null  $message
     * @param  mixed  $code
     * @return \Illuminate\Auth\Access\Response
     */
    public static function denyWithStatus($status, $message = null, $code = null)
    {
        return static::deny($message, $code)->withStatus($status);
    }

    /**
     * Create a new "deny" Response with a 404 HTTP status code.
	 * 创建一个新的带有404 HTTP状态码的"deny"响应。
     *
     * @param  string|null  $message
     * @param  mixed  $code
     * @return \Illuminate\Auth\Access\Response
     */
    public static function denyAsNotFound($message = null, $code = null)
    {
        return static::denyWithStatus(404, $message, $code);
    }

    /**
     * Determine if the response was allowed.
	 * 确定是否响应被允许
     *
     * @return bool
     */
    public function allowed()
    {
        return $this->allowed;
    }

    /**
     * Determine if the response was denied.
	 * 确定是否响应被拒绝
     *
     * @return bool
     */
    public function denied()
    {
        return ! $this->allowed();
    }

    /**
     * Get the response message.
	 * 获取响应消息
     *
     * @return string|null
     */
    public function message()
    {
        return $this->message;
    }

    /**
     * Get the response code / reason.
	 * 获取响应代码或原因
     *
     * @return mixed
     */
    public function code()
    {
        return $this->code;
    }

    /**
     * Throw authorization exception if response was denied.
	 * 如果拒绝响应，则抛出授权异常。
     *
     * @return \Illuminate\Auth\Access\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorize()
    {
        if ($this->denied()) {
            throw (new AuthorizationException($this->message(), $this->code()))
                ->setResponse($this)
                ->withStatus($this->status);
        }

        return $this;
    }

    /**
     * Set the HTTP response status code.
	 * 设置HTTP响应状态码
     *
     * @param  null|int  $status
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
     * Convert the response to an array.
	 * 转换响应为数组
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'allowed' => $this->allowed(),
            'message' => $this->message(),
            'code' => $this->code(),
        ];
    }

    /**
     * Get the string representation of the message.
	 * 获取消息的字符串表示形式
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->message();
    }
}
