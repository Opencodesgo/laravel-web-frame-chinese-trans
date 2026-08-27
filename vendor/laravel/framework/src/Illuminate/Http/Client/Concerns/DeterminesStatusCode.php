<?php
/**
 * Illuminate，Http，客户端，问题，确定状态码
 */

namespace Illuminate\Http\Client\Concerns;

trait DeterminesStatusCode
{
    /**
     * Determine if the response code was 200 "OK" response.
	 * 确定响应代码是否为200"OK"响应
     *
     * @return bool
     */
    public function ok()
    {
        return $this->status() === 200;
    }

    /**
     * Determine if the response code was 201 "Created" response.
	 * 确定响应代码是否为201"已创建"响应
     *
     * @return bool
     */
    public function created()
    {
        return $this->status() === 201;
    }

    /**
     * Determine if the response code was 202 "Accepted" response.
	 * 确定响应代码是否为202"已接受"响应
     *
     * @return bool
     */
    public function accepted()
    {
        return $this->status() === 202;
    }

    /**
     * Determine if the response code was the given status code and the body has no content.
	 * 确定响应代码是否为给定的状态代码，并且正文是否没有内容。
     *
     * @param  int  $status
     * @return bool
     */
    public function noContent($status = 204)
    {
        return $this->status() === $status && $this->body() === '';
    }

    /**
     * Determine if the response code was a 301 "Moved Permanently".
	 * 确定响应代码是否为301"永久移动"
     *
     * @return bool
     */
    public function movedPermanently()
    {
        return $this->status() === 301;
    }

    /**
     * Determine if the response code was a 302 "Found" response.
	 * 确定响应代码是否为302"Found"响应
     *
     * @return bool
     */
    public function found()
    {
        return $this->status() === 302;
    }

    /**
     * Determine if the response was a 400 "Bad Request" response.
	 * 确定响应是否为400"错误请求"响应
     *
     * @return bool
     */
    public function badRequest()
    {
        return $this->status() === 400;
    }

    /**
     * Determine if the response was a 401 "Unauthorized" response.
	 * 确定响应是否为401"未授权"响应
     *
     * @return bool
     */
    public function unauthorized()
    {
        return $this->status() === 401;
    }

    /**
     * Determine if the response was a 402 "Payment Required" response.
	 * 确定响应是否为402"付款要求"响应
     *
     * @return bool
     */
    public function paymentRequired()
    {
        return $this->status() === 402;
    }

    /**
     * Determine if the response was a 403 "Forbidden" response.
	 * 确定响应是否为403"Forbidden"响应
     *
     * @return bool
     */
    public function forbidden()
    {
        return $this->status() === 403;
    }

    /**
     * Determine if the response was a 404 "Not Found" response.
	 * 确定响应是否是404"Not Found"响应
     *
     * @return bool
     */
    public function notFound()
    {
        return $this->status() === 404;
    }

    /**
     * Determine if the response was a 408 "Request Timeout" response.
	 * 确定响应是否为408"请求超时"响应
     *
     * @return bool
     */
    public function requestTimeout()
    {
        return $this->status() === 408;
    }

    /**
     * Determine if the response was a 409 "Conflict" response.
     * 确定响应是否为409"冲突"响应
     * @return bool
     */
    public function conflict()
    {
        return $this->status() === 409;
    }

    /**
     * Determine if the response was a 422 "Unprocessable Entity" response.
	 * 确定响应是否为422"不可处理实体"响应
     *
     * @return bool
     */
    public function unprocessableEntity()
    {
        return $this->status() === 422;
    }

    /**
     * Determine if the response was a 429 "Too Many Requests" response.
	 * 确定响应是否为429"请求过多"响应
     *
     * @return bool
     */
    public function tooManyRequests()
    {
        return $this->status() === 429;
    }
}
