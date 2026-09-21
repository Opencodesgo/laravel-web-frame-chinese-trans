<?php
/**
 * Illuminate, 测试, 问题，断言状态码
 */

namespace Illuminate\Testing\Concerns;

use Illuminate\Testing\Assert as PHPUnit;

trait AssertsStatusCodes
{
    /**
     * Assert that the response has a 200 "OK" status code.
	 * 断言响应的状态码为200"OK"
     *
     * @return $this
     */
    public function assertOk()
    {
        return $this->assertStatus(200);
    }

    /**
     * Assert that the response has a 201 "Created" status code.
	 * 断言响应具有201"已创建"状态码
     *
     * @return $this
     */
    public function assertCreated()
    {
        return $this->assertStatus(201);
    }

    /**
     * Assert that the response has a 202 "Accepted" status code.
	 * 断言响应具有202"已接受"状态码
     *
     * @return $this
     */
    public function assertAccepted()
    {
        return $this->assertStatus(202);
    }

    /**
     * Assert that the response has the given status code and no content.
	 * 断言响应具有给定的状态码而没有内容
     *
     * @param  int  $status
     * @return $this
     */
    public function assertNoContent($status = 204)
    {
        $this->assertStatus($status);

        PHPUnit::assertEmpty($this->getContent(), 'Response content is not empty.');

        return $this;
    }

    /**
     * Assert that the response has a 301 "Moved Permanently" status code.
	 * 断言响应具有301"永久移动"状态码
     *
     * @param  int  $status
     * @return $this
     */
    public function assertMovedPermanently()
    {
        return $this->assertStatus(301);
    }

    /**
     * Assert that the response has a 302 "Found" status code.
	 * 断言响应具有302"Found"状态码
     *
     * @param  int  $status
     * @return $this
     */
    public function assertFound()
    {
        return $this->assertStatus(302);
    }

    /**
     * Assert that the response has a 400 "Bad Request" status code.
	 * 断言响应的状态码为400"错误请求"
     *
     * @return $this
     */
    public function assertBadRequest()
    {
        return $this->assertStatus(400);
    }

    /**
     * Assert that the response has a 401 "Unauthorized" status code.
	 * 断言响应具有401“未授权”状态码
     *
     * @return $this
     */
    public function assertUnauthorized()
    {
        return $this->assertStatus(401);
    }

    /**
     * Assert that the response has a 402 "Payment Required" status code.
	 * 断言响应具有402“Payment Required”状态码
     *
     * @return $this
     */
    public function assertPaymentRequired()
    {
        return $this->assertStatus(402);
    }

    /**
     * Assert that the response has a 403 "Forbidden" status code.
	 * 断言响应具有403“Forbidden”状态码
     *
     * @return $this
     */
    public function assertForbidden()
    {
        return $this->assertStatus(403);
    }

    /**
     * Assert that the response has a 404 "Not Found" status code.
	 * 断言响应具有404“未找到”状态码
     *
     * @return $this
     */
    public function assertNotFound()
    {
        return $this->assertStatus(404);
    }

    /**
     * Assert that the response has a 408 "Request Timeout" status code.
	 * 断言响应具有408“请求超时”状态码
     *
     * @return $this
     */
    public function assertRequestTimeout()
    {
        return $this->assertStatus(408);
    }

    /**
     * Assert that the response has a 409 "Conflict" status code.
	 * 断言响应具有409“冲突”状态码
     *
     * @return $this
     */
    public function assertConflict()
    {
        return $this->assertStatus(409);
    }

    /**
     * Assert that the response has a 422 "Unprocessable Entity" status code.
	 * 断言响应具有422"不可处理实体"状态码
     *
     * @return $this
     */
    public function assertUnprocessable()
    {
        return $this->assertStatus(422);
    }

    /**
     * Assert that the response has a 429 "Too Many Requests" status code.
	 * 断言响应的状态码为429"请求太多"
     *
     * @return $this
     */
    public function assertTooManyRequests()
    {
        return $this->assertStatus(429);
    }
}
