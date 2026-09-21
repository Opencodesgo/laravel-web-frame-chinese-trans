<?php
/**
 * Illuminate，Http，客户端，工厂
 */

namespace Illuminate\Http\Client;

use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response as Psr7Response;
use GuzzleHttp\TransferStats;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use PHPUnit\Framework\Assert as PHPUnit;

/**
 * @mixin \Illuminate\Http\Client\PendingRequest
 */
class Factory
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * The event dispatcher implementation.
	 * 事件分派器实现
     *
     * @var \Illuminate\Contracts\Events\Dispatcher|null
     */
    protected $dispatcher;

    /**
     * The stub callables that will handle requests.
	 * 处理请求的存根可调用对象
     *
     * @var \Illuminate\Support\Collection
     */
    protected $stubCallbacks;

    /**
     * Indicates if the factory is recording requests and responses.
	 * 指示工厂是否正在记录请求和响应
     *
     * @var bool
     */
    protected $recording = false;

    /**
     * The recorded response array.
	 * 记录的响应数组
     *
     * @var array
     */
    protected $recorded = [];

    /**
     * All created response sequences.
	 * 所有创建的响应序列
     *
     * @var array
     */
    protected $responseSequences = [];

    /**
     * Indicates that an exception should be thrown if any request is not faked.
	 * 指示如果未伪造任何请求，则应抛出异常。
     *
     * @var bool
     */
    protected $preventStrayRequests = false;

    /**
     * Create a new factory instance.
	 * 创建新的工厂实例
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher|null  $dispatcher
     * @return void
     */
    public function __construct(Dispatcher $dispatcher = null)
    {
        $this->dispatcher = $dispatcher;

        $this->stubCallbacks = collect();
    }

    /**
     * Create a new response instance for use during stubbing.
	 * 创建一个新的响应实例，以便在存根期间使用。
     *
     * @param  array|string|null  $body
     * @param  int  $status
     * @param  array  $headers
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public static function response($body = null, $status = 200, $headers = [])
    {
        if (is_array($body)) {
            $body = json_encode($body);

            $headers['Content-Type'] = 'application/json';
        }

        $response = new Psr7Response($status, $headers, $body);

        return class_exists(\GuzzleHttp\Promise\Create::class)
            ? \GuzzleHttp\Promise\Create::promiseFor($response)
            : \GuzzleHttp\Promise\promise_for($response);
    }

    /**
     * Get an invokable object that returns a sequence of responses in order for use during stubbing.
	 * 获取一个可调用对象，该对象返回一系列响应，以便在存根期间使用。
     *
     * @param  array  $responses
     * @return \Illuminate\Http\Client\ResponseSequence
     */
    public function sequence(array $responses = [])
    {
        return $this->responseSequences[] = new ResponseSequence($responses);
    }

    /**
     * Register a stub callable that will intercept requests and be able to return stub responses.
	 * 注册一个可调用的存根，它将拦截请求并能够返回存根响应。
     *
     * @param  callable|array|null  $callback
     * @return $this
     */
    public function fake($callback = null)
    {
        $this->record();

        $this->recorded = [];

        if (is_null($callback)) {
            $callback = function () {
                return static::response();
            };
        }

        if (is_array($callback)) {
            foreach ($callback as $url => $callable) {
                $this->stubUrl($url, $callable);
            }

            return $this;
        }

        $this->stubCallbacks = $this->stubCallbacks->merge(collect([
            function ($request, $options) use ($callback) {
                $response = $callback instanceof Closure
                                ? $callback($request, $options)
                                : $callback;

                if ($response instanceof PromiseInterface) {
                    $options['on_stats'](new TransferStats(
                        $request->toPsrRequest(),
                        $response->wait(),
                    ));
                }

                return $response;
            },
        ]));

        return $this;
    }

    /**
     * Register a response sequence for the given URL pattern.
	 * 为给定的URL模式注册响应序列
     *
     * @param  string  $url
     * @return \Illuminate\Http\Client\ResponseSequence
     */
    public function fakeSequence($url = '*')
    {
        return tap($this->sequence(), function ($sequence) use ($url) {
            $this->fake([$url => $sequence]);
        });
    }

    /**
     * Stub the given URL using the given callback.
	 * 使用给定的回调存根给定的URL
     *
     * @param  string  $url
     * @param  \Illuminate\Http\Client\Response|\GuzzleHttp\Promise\PromiseInterface|callable  $callback
     * @return $this
     */
    public function stubUrl($url, $callback)
    {
        return $this->fake(function ($request, $options) use ($url, $callback) {
            if (! Str::is(Str::start($url, '*'), $request->url())) {
                return;
            }

            return $callback instanceof Closure || $callback instanceof ResponseSequence
                        ? $callback($request, $options)
                        : $callback;
        });
    }

    /**
     * Indicate that an exception should be thrown if any request is not faked.
	 * 指示如果未伪造任何请求，则应抛出异常。
     *
     * @param  bool  $prevent
     * @return $this
     */
    public function preventStrayRequests($prevent = true)
    {
        $this->preventStrayRequests = $prevent;

        return $this;
    }

    /**
     * Indicate that an exception should not be thrown if any request is not faked.
	 * 指示如果没有伪造任何请求，则不应抛出异常。
     *
     * @return $this
     */
    public function allowStrayRequests()
    {
        return $this->preventStrayRequests(false);
    }

    /**
     * Begin recording request / response pairs.
	 * 开始记录请求/响应对
     *
     * @return $this
     */
    protected function record()
    {
        $this->recording = true;

        return $this;
    }

    /**
     * Record a request response pair.
	 * 记录一个请求响应对
     *
     * @param  \Illuminate\Http\Client\Request  $request
     * @param  \Illuminate\Http\Client\Response  $response
     * @return void
     */
    public function recordRequestResponsePair($request, $response)
    {
        if ($this->recording) {
            $this->recorded[] = [$request, $response];
        }
    }

    /**
     * Assert that a request / response pair was recorded matching a given truth test.
	 * 断言记录了与给定真值测试匹配的请求/响应对
     *
     * @param  callable  $callback
     * @return void
     */
    public function assertSent($callback)
    {
        PHPUnit::assertTrue(
            $this->recorded($callback)->count() > 0,
            'An expected request was not recorded.'
        );
    }

    /**
     * Assert that the given request was sent in the given order.
	 * 断言给定请求是按给定顺序发送的
     *
     * @param  array  $callbacks
     * @return void
     */
    public function assertSentInOrder($callbacks)
    {
        $this->assertSentCount(count($callbacks));

        foreach ($callbacks as $index => $url) {
            $callback = is_callable($url) ? $url : function ($request) use ($url) {
                return $request->url() == $url;
            };

            PHPUnit::assertTrue($callback(
                $this->recorded[$index][0],
                $this->recorded[$index][1]
            ), 'An expected request (#'.($index + 1).') was not recorded.');
        }
    }

    /**
     * Assert that a request / response pair was not recorded matching a given truth test.
	 * 断言记录的请求/响应对与给定的真值测试不匹配
     *
     * @param  callable  $callback
     * @return void
     */
    public function assertNotSent($callback)
    {
        PHPUnit::assertFalse(
            $this->recorded($callback)->count() > 0,
            'Unexpected request was recorded.'
        );
    }

    /**
     * Assert that no request / response pair was recorded.
	 * 断言没有记录请求/响应对
     *
     * @return void
     */
    public function assertNothingSent()
    {
        PHPUnit::assertEmpty(
            $this->recorded,
            'Requests were recorded.'
        );
    }

    /**
     * Assert how many requests have been recorded.
	 * 断言已经记录了多少请求
     *
     * @param  int  $count
     * @return void
     */
    public function assertSentCount($count)
    {
        PHPUnit::assertCount($count, $this->recorded);
    }

    /**
     * Assert that every created response sequence is empty.
	 * 断言每个创建的响应序列都为空
     *
     * @return void
     */
    public function assertSequencesAreEmpty()
    {
        foreach ($this->responseSequences as $responseSequence) {
            PHPUnit::assertTrue(
                $responseSequence->isEmpty(),
                'Not all response sequences are empty.'
            );
        }
    }

    /**
     * Get a collection of the request / response pairs matching the given truth test.
	 * 获取与给定真值测试匹配的请求/响应对的集合
     *
     * @param  callable  $callback
     * @return \Illuminate\Support\Collection
     */
    public function recorded($callback = null)
    {
        if (empty($this->recorded)) {
            return collect();
        }

        $callback = $callback ?: function () {
            return true;
        };

        return collect($this->recorded)->filter(function ($pair) use ($callback) {
            return $callback($pair[0], $pair[1]);
        });
    }

    /**
     * Create a new pending request instance for this factory.
	 * 为此工厂创建一个新的挂起请求实例
     *
     * @return \Illuminate\Http\Client\PendingRequest
     */
    protected function newPendingRequest()
    {
        return new PendingRequest($this);
    }

    /**
     * Get the current event dispatcher implementation.
	 * 获取当前事件调度程序实现
     *
     * @return \Illuminate\Contracts\Events\Dispatcher|null
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Execute a method against a new pending request instance.
	 * 针对新的挂起请求实例执行方法
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return tap($this->newPendingRequest(), function ($request) {
            $request->stub($this->stubCallbacks)->preventStrayRequests($this->preventStrayRequests);
        })->{$method}(...$parameters);
    }
}
