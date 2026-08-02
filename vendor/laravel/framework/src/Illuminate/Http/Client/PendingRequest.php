<?php
/**
 * Illuminate，Http，客户端，等待请求
 */

namespace Illuminate\Http\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\HandlerStack;
use Illuminate\Http\Client\Events\ConnectionFailed;
use Illuminate\Http\Client\Events\RequestSending;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\VarDumper\VarDumper;

class PendingRequest
{
    use Conditionable, Macroable;

    /**
     * The factory instance.
	 * 工厂实例
     *
     * @var \Illuminate\Http\Client\Factory|null
     */
    protected $factory;

    /**
     * The Guzzle client instance.
	 * Guzzle客户端实例
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * The base URL for the request.
	 * 请求的基URL
     *
     * @var string
     */
    protected $baseUrl = '';

    /**
     * The request body format.
	 * 请求体格式
     *
     * @var string
     */
    protected $bodyFormat;

    /**
     * The raw body for the request.
	 * 请求的原始主体
     *
     * @var string
     */
    protected $pendingBody;

    /**
     * The pending files for the request.
	 * 请求的挂起文件
     *
     * @var array
     */
    protected $pendingFiles = [];

    /**
     * The request cookies.
	 * 请求cookie
     *
     * @var array
     */
    protected $cookies;

    /**
     * The transfer stats for the request.
	 * 请求的传输统计信息
     *
     * \GuzzleHttp\TransferStats
     */
    protected $transferStats;

    /**
     * The request options.
	 * 请求选项
     *
     * @var array
     */
    protected $options = [];

    /**
     * The number of times to try the request.
	 * 尝试请求的次数
     *
     * @var int
     */
    protected $tries = 1;

    /**
     * The number of milliseconds to wait between retries.
	 * 重试之间等待的毫秒数
     *
     * @var int
     */
    protected $retryDelay = 100;

    /**
     * The callback that will determine if the request should be retried.
	 * 确定是否应该重试请求的回调函数
     *
     * @var callable|null
     */
    protected $retryWhenCallback = null;

    /**
     * The callbacks that should execute before the request is sent.
	 * 在发送请求之前应该执行的回调函数
     *
     * @var \Illuminate\Support\Collection
     */
    protected $beforeSendingCallbacks;

    /**
     * The stub callables that will handle requests.
	 * 处理请求的存根可调用对象
     *
     * @var \Illuminate\Support\Collection|null
     */
    protected $stubCallbacks;

    /**
     * The middleware callables added by users that will handle requests.
	 * 由用户添加的中间件可调用项，用于处理请求。
     *
     * @var \Illuminate\Support\Collection
     */
    protected $middleware;

    /**
     * Whether the requests should be asynchronous.
	 * 请求是否应该是异步的
     *
     * @var bool
     */
    protected $async = false;

    /**
     * The pending request promise.
	 * 待处理请求承诺
     *
     * @var \GuzzleHttp\Promise\PromiseInterface
     */
    protected $promise;

    /**
     * The sent request object, if a request has been made.
	 * 如果发出了请求，则发送的请求对象。
     *
     * @var \Illuminate\Http\Client\Request|null
     */
    protected $request;

    /**
     * The Guzzle request options that are mergable via array_merge_recursive.
	 * 通过array_merge_recursive可以合并的Guzzle请求选项
     *
     * @var array
     */
    protected $mergableOptions = [
        'cookies',
        'form_params',
        'headers',
        'json',
        'multipart',
        'query',
    ];

    /**
     * Create a new HTTP Client instance.
	 * 创建一个新的HTTP Client实例
     *
     * @param  \Illuminate\Http\Client\Factory|null  $factory
     * @return void
     */
    public function __construct(Factory $factory = null)
    {
        $this->factory = $factory;
        $this->middleware = new Collection;

        $this->asJson();

        $this->options = [
            'http_errors' => false,
        ];

        $this->beforeSendingCallbacks = collect([function (Request $request, array $options, PendingRequest $pendingRequest) {
            $pendingRequest->request = $request;
            $pendingRequest->cookies = $options['cookies'];

            $pendingRequest->dispatchRequestSendingEvent();
        }]);
    }

    /**
     * Set the base URL for the pending request.
	 * 为挂起的请求设置基本URL
     *
     * @param  string  $url
     * @return $this
     */
    public function baseUrl(string $url)
    {
        $this->baseUrl = $url;

        return $this;
    }

    /**
     * Attach a raw body to the request.
	 * 将原始主体附加到请求
     *
     * @param  string  $content
     * @param  string  $contentType
     * @return $this
     */
    public function withBody($content, $contentType)
    {
        $this->bodyFormat('body');

        $this->pendingBody = $content;

        $this->contentType($contentType);

        return $this;
    }

    /**
     * Indicate the request contains JSON.
	 * 指示请求包含JSON
     *
     * @return $this
     */
    public function asJson()
    {
        return $this->bodyFormat('json')->contentType('application/json');
    }

    /**
     * Indicate the request contains form parameters.
	 * 指示请求包含表单参数
     *
     * @return $this
     */
    public function asForm()
    {
        return $this->bodyFormat('form_params')->contentType('application/x-www-form-urlencoded');
    }

    /**
     * Attach a file to the request.
	 * 向请求附加一个文件
     *
     * @param  string|array  $name
     * @param  string|resource  $contents
     * @param  string|null  $filename
     * @param  array  $headers
     * @return $this
     */
    public function attach($name, $contents = '', $filename = null, array $headers = [])
    {
        if (is_array($name)) {
            foreach ($name as $file) {
                $this->attach(...$file);
            }

            return $this;
        }

        $this->asMultipart();

        $this->pendingFiles[] = array_filter([
            'name' => $name,
            'contents' => $contents,
            'headers' => $headers,
            'filename' => $filename,
        ]);

        return $this;
    }

    /**
     * Indicate the request is a multi-part form request.
	 * 指示请求是一个多部分表单请求
     *
     * @return $this
     */
    public function asMultipart()
    {
        return $this->bodyFormat('multipart');
    }

    /**
     * Specify the body format of the request.
	 * 指定请求的正文格式
     *
     * @param  string  $format
     * @return $this
     */
    public function bodyFormat(string $format)
    {
        return tap($this, function ($request) use ($format) {
            $this->bodyFormat = $format;
        });
    }

    /**
     * Specify the request's content type.
	 * 指定请求的内容类型
     *
     * @param  string  $contentType
     * @return $this
     */
    public function contentType(string $contentType)
    {
        return $this->withHeaders(['Content-Type' => $contentType]);
    }

    /**
     * Indicate that JSON should be returned by the server.
	 * 指示服务器应该返回JSON
     *
     * @return $this
     */
    public function acceptJson()
    {
        return $this->accept('application/json');
    }

    /**
     * Indicate the type of content that should be returned by the server.
	 * 指示服务器应该返回的内容类型
     *
     * @param  string  $contentType
     * @return $this
     */
    public function accept($contentType)
    {
        return $this->withHeaders(['Accept' => $contentType]);
    }

    /**
     * Add the given headers to the request.
	 * 将给定的头部添加到请求中
     *
     * @param  array  $headers
     * @return $this
     */
    public function withHeaders(array $headers)
    {
        return tap($this, function ($request) use ($headers) {
            return $this->options = array_merge_recursive($this->options, [
                'headers' => $headers,
            ]);
        });
    }

    /**
     * Specify the basic authentication username and password for the request.
	 * 为请求指定基本身份验证用户名和密码
     *
     * @param  string  $username
     * @param  string  $password
     * @return $this
     */
    public function withBasicAuth(string $username, string $password)
    {
        return tap($this, function ($request) use ($username, $password) {
            return $this->options['auth'] = [$username, $password];
        });
    }

    /**
     * Specify the digest authentication username and password for the request.
	 * 为请求指定摘要身份验证用户名和密码
     *
     * @param  string  $username
     * @param  string  $password
     * @return $this
     */
    public function withDigestAuth($username, $password)
    {
        return tap($this, function ($request) use ($username, $password) {
            return $this->options['auth'] = [$username, $password, 'digest'];
        });
    }

    /**
     * Specify an authorization token for the request.
	 * 为请求指定授权令牌
     *
     * @param  string  $token
     * @param  string  $type
     * @return $this
     */
    public function withToken($token, $type = 'Bearer')
    {
        return tap($this, function ($request) use ($token, $type) {
            return $this->options['headers']['Authorization'] = trim($type.' '.$token);
        });
    }

    /**
     * Specify the user agent for the request.
	 * 为请求指定用户代理
     *
     * @param  string  $userAgent
     * @return $this
     */
    public function withUserAgent($userAgent)
    {
        return tap($this, function ($request) use ($userAgent) {
            return $this->options['headers']['User-Agent'] = trim($userAgent);
        });
    }

    /**
     * Specify the cookies that should be included with the request.
	 * 指定请求中应该包含的cookie
     *
     * @param  array  $cookies
     * @param  string  $domain
     * @return $this
     */
    public function withCookies(array $cookies, string $domain)
    {
        return tap($this, function ($request) use ($cookies, $domain) {
            return $this->options = array_merge_recursive($this->options, [
                'cookies' => CookieJar::fromArray($cookies, $domain),
            ]);
        });
    }

    /**
     * Indicate that redirects should not be followed.
	 * 指示不应遵循重定向
     *
     * @return $this
     */
    public function withoutRedirecting()
    {
        return tap($this, function ($request) {
            return $this->options['allow_redirects'] = false;
        });
    }

    /**
     * Indicate that TLS certificates should not be verified.
	 * 指示不应该验证TLS证书
     *
     * @return $this
     */
    public function withoutVerifying()
    {
        return tap($this, function ($request) {
            return $this->options['verify'] = false;
        });
    }

    /**
     * Specify the path where the body of the response should be stored.
	 * 指定应该存储响应主体的路径
     *
     * @param  string|resource  $to
     * @return $this
     */
    public function sink($to)
    {
        return tap($this, function ($request) use ($to) {
            return $this->options['sink'] = $to;
        });
    }

    /**
     * Specify the timeout (in seconds) for the request.
	 * 指定请求的超时（以秒为单位）
     *
     * @param  int  $seconds
     * @return $this
     */
    public function timeout(int $seconds)
    {
        return tap($this, function () use ($seconds) {
            $this->options['timeout'] = $seconds;
        });
    }

    /**
     * Specify the number of times the request should be attempted.
	 * 指定应该尝试请求的次数
     *
     * @param  int  $times
     * @param  int  $sleep
     * @param  callable|null  $when
     * @return $this
     */
    public function retry(int $times, int $sleep = 0, ?callable $when = null)
    {
        $this->tries = $times;
        $this->retryDelay = $sleep;
        $this->retryWhenCallback = $when;

        return $this;
    }

    /**
     * Replace the specified options on the request.
	 * 替换请求上指定的选项
     *
     * @param  array  $options
     * @return $this
     */
    public function withOptions(array $options)
    {
        return tap($this, function ($request) use ($options) {
            return $this->options = array_replace_recursive(
                array_merge_recursive($this->options, Arr::only($options, $this->mergableOptions)),
                $options
            );
        });
    }

    /**
     * Add new middleware the client handler stack.
	 * 在客户机处理程序堆栈中添加新的中间件
     *
     * @param  callable  $middleware
     * @return $this
     */
    public function withMiddleware(callable $middleware)
    {
        $this->middleware->push($middleware);

        return $this;
    }

    /**
     * Add a new "before sending" callback to the request.
	 * 向请求添加一个新的"发送前"回调
     *
     * @param  callable  $callback
     * @return $this
     */
    public function beforeSending($callback)
    {
        return tap($this, function () use ($callback) {
            $this->beforeSendingCallbacks[] = $callback;
        });
    }

    /**
     * Dump the request before sending.
	 * 发送请求前转储请求
     *
     * @return $this
     */
    public function dump()
    {
        $values = func_get_args();

        return $this->beforeSending(function (Request $request, array $options) use ($values) {
            foreach (array_merge($values, [$request, $options]) as $value) {
                VarDumper::dump($value);
            }
        });
    }

    /**
     * Dump the request before sending and end the script.
	 * 在发送请求之前转储请求并结束脚本
     *
     * @return $this
     */
    public function dd()
    {
        $values = func_get_args();

        return $this->beforeSending(function (Request $request, array $options) use ($values) {
            foreach (array_merge($values, [$request, $options]) as $value) {
                VarDumper::dump($value);
            }

            exit(1);
        });
    }

    /**
     * Issue a GET request to the given URL.
	 * 向给定的URL发出GET请求
     *
     * @param  string  $url
     * @param  array|string|null  $query
     * @return \Illuminate\Http\Client\Response
     */
    public function get(string $url, $query = null)
    {
        return $this->send('GET', $url, func_num_args() === 1 ? [] : [
            'query' => $query,
        ]);
    }

    /**
     * Issue a HEAD request to the given URL.
	 * 向给定的URL发出一个HEAD请求
     *
     * @param  string  $url
     * @param  array|string|null  $query
     * @return \Illuminate\Http\Client\Response
     */
    public function head(string $url, $query = null)
    {
        return $this->send('HEAD', $url, func_num_args() === 1 ? [] : [
            'query' => $query,
        ]);
    }

    /**
     * Issue a POST request to the given URL.
	 * 向给定的URL发出POST请求
     *
     * @param  string  $url
     * @param  array  $data
     * @return \Illuminate\Http\Client\Response
     */
    public function post(string $url, array $data = [])
    {
        return $this->send('POST', $url, [
            $this->bodyFormat => $data,
        ]);
    }

    /**
     * Issue a PATCH request to the given URL.
	 * 向给定的URL发出PATCH请求
     *
     * @param  string  $url
     * @param  array  $data
     * @return \Illuminate\Http\Client\Response
     */
    public function patch($url, $data = [])
    {
        return $this->send('PATCH', $url, [
            $this->bodyFormat => $data,
        ]);
    }

    /**
     * Issue a PUT request to the given URL.
	 * 向给定的URL发出一个PUT请求
     *
     * @param  string  $url
     * @param  array  $data
     * @return \Illuminate\Http\Client\Response
     */
    public function put($url, $data = [])
    {
        return $this->send('PUT', $url, [
            $this->bodyFormat => $data,
        ]);
    }

    /**
     * Issue a DELETE request to the given URL.
	 * 向给定的URL发出DELETE请求
     *
     * @param  string  $url
     * @param  array  $data
     * @return \Illuminate\Http\Client\Response
     */
    public function delete($url, $data = [])
    {
        return $this->send('DELETE', $url, empty($data) ? [] : [
            $this->bodyFormat => $data,
        ]);
    }

    /**
     * Send a pool of asynchronous requests concurrently.
	 * 并发发送异步请求池
     *
     * @param  callable  $callback
     * @return array
     */
    public function pool(callable $callback)
    {
        $results = [];

        $requests = tap(new Pool($this->factory), $callback)->getRequests();

        foreach ($requests as $key => $item) {
            $results[$key] = $item instanceof static ? $item->getPromise()->wait() : $item->wait();
        }

        return $results;
    }

    /**
     * Send the request to the given URL.
	 * 将请求发送到给定的URL
     *
     * @param  string  $method
     * @param  string  $url
     * @param  array  $options
     * @return \Illuminate\Http\Client\Response
     *
     * @throws \Exception
     */
    public function send(string $method, string $url, array $options = [])
    {
        $url = ltrim(rtrim($this->baseUrl, '/').'/'.ltrim($url, '/'), '/');

        if (isset($options[$this->bodyFormat])) {
            if ($this->bodyFormat === 'multipart') {
                $options[$this->bodyFormat] = $this->parseMultipartBodyFormat($options[$this->bodyFormat]);
            } elseif ($this->bodyFormat === 'body') {
                $options[$this->bodyFormat] = $this->pendingBody;
            }

            if (is_array($options[$this->bodyFormat])) {
                $options[$this->bodyFormat] = array_merge(
                    $options[$this->bodyFormat], $this->pendingFiles
                );
            }
        } else {
            $options[$this->bodyFormat] = $this->pendingBody;
        }

        [$this->pendingBody, $this->pendingFiles] = [null, []];

        if ($this->async) {
            return $this->makePromise($method, $url, $options);
        }

        return retry($this->tries ?? 1, function () use ($method, $url, $options) {
            try {
                return tap(new Response($this->sendRequest($method, $url, $options)), function ($response) {
                    $this->populateResponse($response);

                    if ($this->tries > 1 && ! $response->successful()) {
                        $response->throw();
                    }

                    $this->dispatchResponseReceivedEvent($response);
                });
            } catch (ConnectException $e) {
                $this->dispatchConnectionFailedEvent();

                throw new ConnectionException($e->getMessage(), 0, $e);
            }
        }, $this->retryDelay ?? 100, $this->retryWhenCallback);
    }

    /**
     * Parse multi-part form data.
	 * 解析多部分表单数据
     *
     * @param  array  $data
     * @return array|array[]
     */
    protected function parseMultipartBodyFormat(array $data)
    {
        return collect($data)->map(function ($value, $key) {
            return is_array($value) ? $value : ['name' => $key, 'contents' => $value];
        })->values()->all();
    }

    /**
     * Send an asynchronous request to the given URL.
	 * 向给定的URL发送异步请求
     *
     * @param  string  $method
     * @param  string  $url
     * @param  array  $options
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    protected function makePromise(string $method, string $url, array $options = [])
    {
        return $this->promise = $this->sendRequest($method, $url, $options)
            ->then(function (MessageInterface $message) {
                return tap(new Response($message), function ($response) {
                    $this->populateResponse($response);
                    $this->dispatchResponseReceivedEvent($response);
                });
            })
            ->otherwise(function (TransferException $e) {
                return $e instanceof RequestException ? $this->populateResponse(new Response($e->getResponse())) : $e;
            });
    }

    /**
     * Send a request either synchronously or asynchronously.
	 * 同步或异步发送请求
     *
     * @param  string  $method
     * @param  string  $url
     * @param  array  $options
     * @return \Psr\Http\Message\MessageInterface|\GuzzleHttp\Promise\PromiseInterface
     *
     * @throws \Exception
     */
    protected function sendRequest(string $method, string $url, array $options = [])
    {
        $clientMethod = $this->async ? 'requestAsync' : 'request';

        $laravelData = $this->parseRequestData($method, $url, $options);

        return $this->buildClient()->$clientMethod($method, $url, $this->mergeOptions([
            'laravel_data' => $laravelData,
            'on_stats' => function ($transferStats) {
                $this->transferStats = $transferStats;
            },
        ], $options));
    }

    /**
     * Get the request data as an array so that we can attach it to the request for convenient assertions.
	 * 将请求数据作为数组获取，以便我们可以将其附加到请求中，以便进行方便的断言。
     *
     * @param  string  $method
     * @param  string  $url
     * @param  array  $options
     * @return array
     */
    protected function parseRequestData($method, $url, array $options)
    {
        $laravelData = $options[$this->bodyFormat] ?? $options['query'] ?? [];

        $urlString = Str::of($url);

        if (empty($laravelData) && $method === 'GET' && $urlString->contains('?')) {
            $laravelData = (string) $urlString->after('?');
        }

        if (is_string($laravelData)) {
            parse_str($laravelData, $parsedData);

            $laravelData = is_array($parsedData) ? $parsedData : [];
        }

        return $laravelData;
    }

    /**
     * Populate the given response with additional data.
	 * 用其他数据填充给定的响应
     *
     * @param  \Illuminate\Http\Client\Response  $response
     * @return \Illuminate\Http\Client\Response
     */
    protected function populateResponse(Response $response)
    {
        $response->cookies = $this->cookies;

        $response->transferStats = $this->transferStats;

        return $response;
    }

    /**
     * Build the Guzzle client.
	 * 构建Guzzle客户端
     *
     * @return \GuzzleHttp\Client
     */
    public function buildClient()
    {
        return $this->requestsReusableClient()
               ? $this->getReusableClient()
               : $this->createClient($this->buildHandlerStack());
    }

    /**
     * Determine if a reusable client is required.
	 * 确定是否需要可重用客户机
     *
     * @return bool
     */
    protected function requestsReusableClient()
    {
        return ! is_null($this->client) || $this->async;
    }

    /**
     * Retrieve a reusable Guzzle client.
	 * 检索可重用的Guzzle客户端
     *
     * @return \GuzzleHttp\Client
     */
    protected function getReusableClient()
    {
        return $this->client = $this->client ?: $this->createClient($this->buildHandlerStack());
    }

    /**
     * Create new Guzzle client.
	 * 创建新的Guzzle客户端
     *
     * @param  \GuzzleHttp\HandlerStack  $handlerStack
     * @return \GuzzleHttp\Client
     */
    public function createClient($handlerStack)
    {
        return new Client([
            'handler' => $handlerStack,
            'cookies' => true,
        ]);
    }

    /**
     * Build the Guzzle client handler stack.
	 * 构建Guzzle客户端处理程序堆栈
     *
     * @return \GuzzleHttp\HandlerStack
     */
    public function buildHandlerStack()
    {
        return $this->pushHandlers(HandlerStack::create());
    }

    /**
     * Add the necessary handlers to the given handler stack.
	 * 将必要的处理程序添加到给定的处理程序堆栈中
     *
     * @param  \GuzzleHttp\HandlerStack  $handlerStack
     * @return \GuzzleHttp\HandlerStack
     */
    public function pushHandlers($handlerStack)
    {
        return tap($handlerStack, function ($stack) {
            $stack->push($this->buildBeforeSendingHandler());
            $stack->push($this->buildRecorderHandler());
            $stack->push($this->buildStubHandler());

            $this->middleware->each(function ($middleware) use ($stack) {
                $stack->push($middleware);
            });
        });
    }

    /**
     * Build the before sending handler.
	 * 构建发送前处理程序
     *
     * @return \Closure
     */
    public function buildBeforeSendingHandler()
    {
        return function ($handler) {
            return function ($request, $options) use ($handler) {
                return $handler($this->runBeforeSendingCallbacks($request, $options), $options);
            };
        };
    }

    /**
     * Build the recorder handler.
	 * 构建记录器处理程序
     *
     * @return \Closure
     */
    public function buildRecorderHandler()
    {
        return function ($handler) {
            return function ($request, $options) use ($handler) {
                $promise = $handler($request, $options);

                return $promise->then(function ($response) use ($request, $options) {
                    optional($this->factory)->recordRequestResponsePair(
                        (new Request($request))->withData($options['laravel_data']),
                        new Response($response)
                    );

                    return $response;
                });
            };
        };
    }

    /**
     * Build the stub handler.
	 * 构建存根处理程序
     *
     * @return \Closure
     */
    public function buildStubHandler()
    {
        return function ($handler) {
            return function ($request, $options) use ($handler) {
                $response = ($this->stubCallbacks ?? collect())
                     ->map
                     ->__invoke((new Request($request))->withData($options['laravel_data']), $options)
                     ->filter()
                     ->first();

                if (is_null($response)) {
                    return $handler($request, $options);
                }

                $response = is_array($response) ? Factory::response($response) : $response;

                $sink = $options['sink'] ?? null;

                if ($sink) {
                    $response->then($this->sinkStubHandler($sink));
                }

                return $response;
            };
        };
    }

    /**
     * Get the sink stub handler callback.
	 * 获取接收存根处理程序回调
     *
     * @param  string  $sink
     * @return \Closure
     */
    protected function sinkStubHandler($sink)
    {
        return function ($response) use ($sink) {
            $body = $response->getBody()->getContents();

            if (is_string($sink)) {
                file_put_contents($sink, $body);

                return;
            }

            fwrite($sink, $body);
            rewind($sink);
        };
    }

    /**
     * Execute the "before sending" callbacks.
	 * 执行"发送之前"回调
     *
     * @param  \GuzzleHttp\Psr7\RequestInterface  $request
     * @param  array  $options
     * @return \GuzzleHttp\Psr7\RequestInterface
     */
    public function runBeforeSendingCallbacks($request, array $options)
    {
        return tap($request, function (&$request) use ($options) {
            $this->beforeSendingCallbacks->each(function ($callback) use (&$request, $options) {
                $callbackResult = call_user_func(
                    $callback, (new Request($request))->withData($options['laravel_data']), $options, $this
                );

                if ($callbackResult instanceof RequestInterface) {
                    $request = $callbackResult;
                } elseif ($callbackResult instanceof Request) {
                    $request = $callbackResult->toPsrRequest();
                }
            });
        });
    }

    /**
     * Replace the given options with the current request options.
	 * 用当前请求选项替换给定的选项
     *
     * @param  array  $options
     * @return array
     */
    public function mergeOptions(...$options)
    {
        return array_replace_recursive(
            array_merge_recursive($this->options, Arr::only($options, $this->mergableOptions)),
            ...$options
        );
    }

    /**
     * Register a stub callable that will intercept requests and be able to return stub responses.
	 * 注册一个可调用的存根，它将拦截请求并能够返回存根响应。
     *
     * @param  callable  $callback
     * @return $this
     */
    public function stub($callback)
    {
        $this->stubCallbacks = collect($callback);

        return $this;
    }

    /**
     * Toggle asynchronicity in requests.
	 * 在请求中切换异步性
     *
     * @param  bool  $async
     * @return $this
     */
    public function async(bool $async = true)
    {
        $this->async = $async;

        return $this;
    }

    /**
     * Retrieve the pending request promise.
	 * 检索挂起的请求承诺
     *
     * @return \GuzzleHttp\Promise\PromiseInterface|null
     */
    public function getPromise()
    {
        return $this->promise;
    }

    /**
     * Dispatch the RequestSending event if a dispatcher is available.
	 * 如果分派器可用，分派RequestSending事件。
     *
     * @return void
     */
    protected function dispatchRequestSendingEvent()
    {
        if ($dispatcher = optional($this->factory)->getDispatcher()) {
            $dispatcher->dispatch(new RequestSending($this->request));
        }
    }

    /**
     * Dispatch the ResponseReceived event if a dispatcher is available.
	 * 如果调度程序可用，则调度responserreceived事件。
     *
     * @param  \Illuminate\Http\Client\Response  $response
     * @return void
     */
    protected function dispatchResponseReceivedEvent(Response $response)
    {
        if (! ($dispatcher = optional($this->factory)->getDispatcher()) ||
            ! $this->request) {
            return;
        }

        $dispatcher->dispatch(new ResponseReceived($this->request, $response));
    }

    /**
     * Dispatch the ConnectionFailed event if a dispatcher is available.
	 * 如果调度程序可用，则调度ConnectionFailed事件。
     *
     * @return void
     */
    protected function dispatchConnectionFailedEvent()
    {
        if ($dispatcher = optional($this->factory)->getDispatcher()) {
            $dispatcher->dispatch(new ConnectionFailed($this->request));
        }
    }

    /**
     * Set the client instance.
	 * 设置客户端实例
     *
     * @param  \GuzzleHttp\Client  $client
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Create a new client instance using the given handler.
	 * 使用给定的处理程序创建一个新的客户机实例
     *
     * @param  callable  $handler
     * @return $this
     */
    public function setHandler($handler)
    {
        $this->client = $this->createClient(
            $this->pushHandlers(HandlerStack::create($handler))
        );

        return $this;
    }

    /**
     * Get the pending request options.
	 * 获取挂起请求选项
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
