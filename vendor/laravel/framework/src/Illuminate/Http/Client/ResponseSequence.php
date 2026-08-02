<?php
/**
 * Illuminate，Http，客户端，响应序列
 */

namespace Illuminate\Http\Client;

use OutOfBoundsException;

class ResponseSequence
{
    /**
     * The responses in the sequence.
	 * 序列中的响应
     *
     * @var array
     */
    protected $responses;

    /**
     * Indicates that invoking this sequence when it is empty should throw an exception.
	 * 指明在该序列为空时调用该序列应抛出异常
     *
     * @var bool
     */
    protected $failWhenEmpty = true;

    /**
     * The response that should be returned when the sequence is empty.
	 * 当序列为空时应该返回的响应
     *
     * @var \GuzzleHttp\Promise\PromiseInterface
     */
    protected $emptyResponse;

    /**
     * Create a new response sequence.
	 * 创建新的响应序列
     *
     * @param  array  $responses
     * @return void
     */
    public function __construct(array $responses)
    {
        $this->responses = $responses;
    }

    /**
     * Push a response to the sequence.
	 * 向序列推送响应
     *
     * @param  string|array  $body
     * @param  int  $status
     * @param  array  $headers
     * @return $this
     */
    public function push($body = '', int $status = 200, array $headers = [])
    {
        $body = is_array($body) ? json_encode($body) : $body;

        return $this->pushResponse(
            Factory::response($body, $status, $headers)
        );
    }

    /**
     * Push a response with the given status code to the sequence.
	 * 将具有给定状态码的响应发送到序列
     *
     * @param  int  $status
     * @param  array  $headers
     * @return $this
     */
    public function pushStatus(int $status, array $headers = [])
    {
        return $this->pushResponse(
            Factory::response('', $status, $headers)
        );
    }

    /**
     * Push response with the contents of a file as the body to the sequence.
	 * 将以文件内容为主体的响应推送到序列
     *
     * @param  string  $filePath
     * @param  int  $status
     * @param  array  $headers
     * @return $this
     */
    public function pushFile(string $filePath, int $status = 200, array $headers = [])
    {
        $string = file_get_contents($filePath);

        return $this->pushResponse(
            Factory::response($string, $status, $headers)
        );
    }

    /**
     * Push a response to the sequence.
	 * 向序列推送响应
     *
     * @param  mixed  $response
     * @return $this
     */
    public function pushResponse($response)
    {
        $this->responses[] = $response;

        return $this;
    }

    /**
     * Make the sequence return a default response when it is empty.
	 * 当序列为空时，使其返回默认响应。
     *
     * @param  \GuzzleHttp\Promise\PromiseInterface|\Closure  $response
     * @return $this
     */
    public function whenEmpty($response)
    {
        $this->failWhenEmpty = false;
        $this->emptyResponse = $response;

        return $this;
    }

    /**
     * Make the sequence return a default response when it is empty.
	 * 当序列为空时，使其返回默认响应。
     *
     * @return $this
     */
    public function dontFailWhenEmpty()
    {
        return $this->whenEmpty(Factory::response());
    }

    /**
     * Indicate that this sequence has depleted all of its responses.
	 * 表明这个序列已经耗尽了所有的响应
     *
     * @return bool
     */
    public function isEmpty()
    {
        return count($this->responses) === 0;
    }

    /**
     * Get the next response in the sequence.
	 * 获取序列中的下一个响应
     *
     * @return mixed
     */
    public function __invoke()
    {
        if ($this->failWhenEmpty && count($this->responses) === 0) {
            throw new OutOfBoundsException('A request was made, but the response sequence is empty.');
        }

        if (! $this->failWhenEmpty && count($this->responses) === 0) {
            return value($this->emptyResponse ?? Factory::response());
        }

        return array_shift($this->responses);
    }
}
