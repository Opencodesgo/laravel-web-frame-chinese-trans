<?php
/**
 * Illuminate，认证，访问，处理授权
 */

namespace Illuminate\Auth\Access;

trait HandlesAuthorization
{
    /**
     * Create a new access response.
	 * 创建新的访问响应
     *
     * @param  string|null  $message
     * @param  mixed  $code
     * @return \Illuminate\Auth\Access\Response
     */
    protected function allow($message = null, $code = null)
    {
        return Response::allow($message, $code);
    }

    /**
     * Throws an unauthorized exception.
	 * 抛出未经授权的异常
     *
     * @param  string|null  $message
     * @param  mixed|null  $code
     * @return \Illuminate\Auth\Access\Response
     */
    protected function deny($message = null, $code = null)
    {
        return Response::deny($message, $code);
    }

    /**
     * Deny with a HTTP status code.
	 * 使用HTTP状态码拒绝
     *
     * @param  int  $status
     * @param  string|null  $message
     * @param  int|null  $code
     * @return \Illuminate\Auth\Access\Response
     */
    public function denyWithStatus($status, $message = null, $code = null)
    {
        return Response::denyWithStatus($status, $message, $code);
    }

    /**
     * Deny with a 404 HTTP status code.
	 * 使用404 HTTP状态码拒绝
     *
     * @param  string|null  $message
     * @param  int|null  $code
     * @return \Illuminate\Auth\Access\Response
     */
    public function denyAsNotFound($message = null, $code = null)
    {
        return Response::denyWithStatus(404, $message, $code);
    }
}
