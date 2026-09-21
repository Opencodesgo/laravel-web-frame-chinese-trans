<?php
/**
 * Illuminate，Http，客户端，响应
 */

namespace Illuminate\Http\Client;

use ArrayAccess;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use LogicException;

class Response implements ArrayAccess
{
    use Concerns\DeterminesStatusCode, Macroable {
        __call as macroCall;
    }

    /**
     * The underlying PSR response.
	 * 底层PSR响应
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * The decoded JSON response.
	 * 解码后的JSON响应
     *
     * @var array
     */
    protected $decoded;

    /**
     * The request cookies.
	 * 请求cookie
     *
     * @var \GuzzleHttp\Cookie\CookieJar
     */
    public $cookies;

    /**
     * The transfer stats for the request.
	 * 请求的传输统计信息
     *
     * @var \GuzzleHttp\TransferStats|null
     */
    public $transferStats;

    /**
     * Create a new response instance.
	 * 创建一个新的响应实例
     *
     * @param  \Psr\Http\Message\MessageInterface  $response
     * @return void
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * Get the body of the response.
	 * 获取响应的主体
     *
     * @return string
     */
    public function body()
    {
        return (string) $this->response->getBody();
    }

    /**
     * Get the JSON decoded body of the response as an array or scalar value.
	 * 以数组或标量值的形式获取响应的JSON解码体
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function json($key = null, $default = null)
    {
        if (! $this->decoded) {
            $this->decoded = json_decode($this->body(), true);
        }

        if (is_null($key)) {
            return $this->decoded;
        }

        return data_get($this->decoded, $key, $default);
    }

    /**
     * Get the JSON decoded body of the response as an object.
	 * 获取响应的JSON解码体作为对象
     *
     * @return object|null
     */
    public function object()
    {
        return json_decode($this->body(), false);
    }

    /**
     * Get the JSON decoded body of the response as a collection.
	 * 获取响应的JSON解码体作为集合
     *
     * @param  string|null  $key
     * @return \Illuminate\Support\Collection
     */
    public function collect($key = null)
    {
        return Collection::make($this->json($key));
    }

    /**
     * Get a header from the response.
	 * 从响应中获取报头
     *
     * @param  string  $header
     * @return string
     */
    public function header(string $header)
    {
        return $this->response->getHeaderLine($header);
    }

    /**
     * Get the headers from the response.
	 * 从响应中获取报头
     *
     * @return array
     */
    public function headers()
    {
        return $this->response->getHeaders();
    }

    /**
     * Get the status code of the response.
	 * 获取响应的状态码
     *
     * @return int
     */
    public function status()
    {
        return (int) $this->response->getStatusCode();
    }

    /**
     * Get the reason phrase of the response.
	 * 获取响应的原因短语
     *
     * @return string
     */
    public function reason()
    {
        return $this->response->getReasonPhrase();
    }

    /**
     * Get the effective URI of the response.
	 * 获取响应的有效URI
     *
     * @return \Psr\Http\Message\UriInterface|null
     */
    public function effectiveUri()
    {
        return $this->transferStats?->getEffectiveUri();
    }

    /**
     * Determine if the request was successful.
	 * 确定请求是否成功
     *
     * @return bool
     */
    public function successful()
    {
        return $this->status() >= 200 && $this->status() < 300;
    }

    /**
     * Determine if the response was a redirect.
	 * 确定响应是否为重定向
     *
     * @return bool
     */
    public function redirect()
    {
        return $this->status() >= 300 && $this->status() < 400;
    }

    /**
     * Determine if the response indicates a client or server error occurred.
	 * 确定响应是否表明发生了客户端或服务器错误
     *
     * @return bool
     */
    public function failed()
    {
        return $this->serverError() || $this->clientError();
    }

    /**
     * Determine if the response indicates a client error occurred.
	 * 确定响应是否表明发生了客户端错误
     *
     * @return bool
     */
    public function clientError()
    {
        return $this->status() >= 400 && $this->status() < 500;
    }

    /**
     * Determine if the response indicates a server error occurred.
	 * 确定响应是否表明发生了服务器错误
     *
     * @return bool
     */
    public function serverError()
    {
        return $this->status() >= 500;
    }

    /**
     * Execute the given callback if there was a server or client error.
	 * 如果存在服务器或客户端错误，则执行给定的回调。
     *
     * @param  callable  $callback
     * @return $this
     */
    public function onError(callable $callback)
    {
        if ($this->failed()) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Get the response cookies.
	 * 得到响应cookies
     *
     * @return \GuzzleHttp\Cookie\CookieJar
     */
    public function cookies()
    {
        return $this->cookies;
    }

    /**
     * Get the handler stats of the response.
	 * 获取响应的处理程序统计信息
     *
     * @return array
     */
    public function handlerStats()
    {
        return $this->transferStats?->getHandlerStats() ?? [];
    }

    /**
     * Close the stream and any underlying resources.
	 * 关闭流和任何底层资源
     *
     * @return $this
     */
    public function close()
    {
        $this->response->getBody()->close();

        return $this;
    }

    /**
     * Get the underlying PSR response for the response.
	 * 获取响应的底层PSR响应
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function toPsrResponse()
    {
        return $this->response;
    }

    /**
     * Create an exception if a server or client error occurred.
	 * 如果发生服务器或客户端错误，则创建异常。
     *
     * @return \Illuminate\Http\Client\RequestException|null
     */
    public function toException()
    {
        if ($this->failed()) {
            return new RequestException($this);
        }
    }

    /**
     * Throw an exception if a server or client error occurred.
	 * 如果发生服务器或客户端错误，则抛出异常。
     *
     * @param  \Closure|null  $callback
     * @return $this
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function throw()
    {
        $callback = func_get_args()[0] ?? null;

        if ($this->failed()) {
            throw tap($this->toException(), function ($exception) use ($callback) {
                if ($callback && is_callable($callback)) {
                    $callback($this, $exception);
                }
            });
        }

        return $this;
    }

    /**
     * Throw an exception if a server or client error occurred and the given condition evaluates to true.
	 * 如果发生服务器或客户端错误并且给定条件的计算结果为true，则抛出异常。
     *
     * @param  \Closure|bool  $condition
     * @param  \Closure|null  $throwCallback
     * @return $this
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function throwIf($condition)
    {
        return value($condition, $this) ? $this->throw(func_get_args()[1] ?? null) : $this;
    }

    /**
     * Throw an exception if the response status code matches the given code.
	 * 如果响应状态码与给定代码匹配，则抛出异常
     *
     * @param  callable|int  $statusCode
     * @return $this
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function throwIfStatus($statusCode)
    {
        if (is_callable($statusCode) &&
            $statusCode($this->status(), $this)) {
            return $this->throw();
        }

        return $this->status() === $statusCode ? $this->throw() : $this;
    }

    /**
     * Throw an exception unless the response status code matches the given code.
	 * 除非响应状态码与给定代码匹配，否则抛出异常。
     *
     * @param  callable|int  $statusCode
     * @return $this
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function throwUnlessStatus($statusCode)
    {
        if (is_callable($statusCode) &&
            ! $statusCode($this->status(), $this)) {
            return $this->throw();
        }

        return $this->status() === $statusCode ? $this : $this->throw();
    }

    /**
     * Throw an exception if the response status code is a 4xx level code.
	 * 如果响应状态码是4xx级代码，则抛出异常。
     *
     * @return $this
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function throwIfClientError()
    {
        return $this->clientError() ? $this->throw() : $this;
    }

    /**
     * Throw an exception if the response status code is a 5xx level code.
	 * 如果响应状态码是5xx级代码，则抛出异常。
     *
     * @return $this
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function throwIfServerError()
    {
        return $this->serverError() ? $this->throw() : $this;
    }

    /**
     * Determine if the given offset exists.
	 * 确定给定的偏移量是否存在
     *
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->json()[$offset]);
    }

    /**
     * Get the value for a given offset.
	 * 获取给定偏移量的值
     *
     * @param  string  $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->json()[$offset];
    }

    /**
     * Set the value at the given offset.
	 * 在给定的偏移量处设置值
     *
     * @param  string  $offset
     * @param  mixed  $value
     * @return void
     *
     * @throws \LogicException
     */
    public function offsetSet($offset, $value): void
    {
        throw new LogicException('Response data may not be mutated using array access.');
    }

    /**
     * Unset the value at the given offset.
	 * 在给定偏移量处取消值的设置
     *
     * @param  string  $offset
     * @return void
     *
     * @throws \LogicException
     */
    public function offsetUnset($offset): void
    {
        throw new LogicException('Response data may not be mutated using array access.');
    }

    /**
     * Get the body of the response.
	 * 获取响应的主体
     *
     * @return string
     */
    public function __toString()
    {
        return $this->body();
    }

    /**
     * Dynamically proxy other methods to the underlying response.
	 * 将其他方法动态代理到底层响应
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return static::hasMacro($method)
                    ? $this->macroCall($method, $parameters)
                    : $this->response->{$method}(...$parameters);
    }
}
