<?php
/**
 * Illuminate, Http, 请求
 */

namespace Illuminate\Http;

use ArrayAccess;
use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Session\SymfonySessionDecorator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use RuntimeException;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @method array validate(array $rules, ...$params)
 * @method array validateWithBag(string $errorBag, array $rules, ...$params)
 * @method bool hasValidSignature(bool $absolute = true)
 */
class Request extends SymfonyRequest implements Arrayable, ArrayAccess
{
    use Concerns\CanBePrecognitive,
        Concerns\InteractsWithContentTypes,
        Concerns\InteractsWithFlashData,
        Concerns\InteractsWithInput,
        Macroable;

    /**
     * The decoded JSON content for the request.
	 * 请求的解码JSON内容
     *
     * @var \Symfony\Component\HttpFoundation\ParameterBag|null
     */
    protected $json;

    /**
     * All of the converted files for the request.
	 * 为请求转换的所有文件
     *
     * @var array
     */
    protected $convertedFiles;

    /**
     * The user resolver callback.
	 * 用户解析器回调
     *
     * @var \Closure
     */
    protected $userResolver;

    /**
     * The route resolver callback.
	 * 路由解析器回调
     *
     * @var \Closure
     */
    protected $routeResolver;

    /**
     * Create a new Illuminate HTTP request from server variables.
	 * 从服务器变量创建一个新的照亮HTTP请求
     *
     * @return static
     */
    public static function capture()
    {
        static::enableHttpMethodParameterOverride();

        return static::createFromBase(SymfonyRequest::createFromGlobals());
    }

    /**
     * Return the Request instance.
	 * 返回Request实例
     *
     * @return $this
     */
    public function instance()
    {
        return $this;
    }

    /**
     * Get the request method.
	 * 获取请求方法
     *
     * @return string
     */
    public function method()
    {
        return $this->getMethod();
    }

    /**
     * Get the root URL for the application.
	 * 获取应用程序的根URL
     *
     * @return string
     */
    public function root()
    {
        return rtrim($this->getSchemeAndHttpHost().$this->getBaseUrl(), '/');
    }

    /**
     * Get the URL (no query string) for the request.
	 * 获取请求的URL（无查询字符串）
     *
     * @return string
     */
    public function url()
    {
        return rtrim(preg_replace('/\?.*/', '', $this->getUri()), '/');
    }

    /**
     * Get the full URL for the request.
	 * 获取请求的完整URL
     *
     * @return string
     */
    public function fullUrl()
    {
        $query = $this->getQueryString();

        $question = $this->getBaseUrl().$this->getPathInfo() === '/' ? '/?' : '?';

        return $query ? $this->url().$question.$query : $this->url();
    }

    /**
     * Get the full URL for the request with the added query string parameters.
	 * 获取带有添加的查询字符串参数的请求的完整URL
     *
     * @param  array  $query
     * @return string
     */
    public function fullUrlWithQuery(array $query)
    {
        $question = $this->getBaseUrl().$this->getPathInfo() === '/' ? '/?' : '?';

        return count($this->query()) > 0
            ? $this->url().$question.Arr::query(array_merge($this->query(), $query))
            : $this->fullUrl().$question.Arr::query($query);
    }

    /**
     * Get the full URL for the request without the given query string parameters.
	 * 获取不含给定查询字符串参数的请求的完整URL
     *
     * @param  array|string  $keys
     * @return string
     */
    public function fullUrlWithoutQuery($keys)
    {
        $query = Arr::except($this->query(), $keys);

        $question = $this->getBaseUrl().$this->getPathInfo() === '/' ? '/?' : '?';

        return count($query) > 0
            ? $this->url().$question.Arr::query($query)
            : $this->url();
    }

    /**
     * Get the current path info for the request.
	 * 获取请求的当前路径信息
     *
     * @return string
     */
    public function path()
    {
        $pattern = trim($this->getPathInfo(), '/');

        return $pattern === '' ? '/' : $pattern;
    }

    /**
     * Get the current decoded path info for the request.
	 * 获取请求的当前已解码路径信息
     *
     * @return string
     */
    public function decodedPath()
    {
        return rawurldecode($this->path());
    }

    /**
     * Get a segment from the URI (1 based index).
	 * 从URI（基于1的索引）获取一个段
     *
     * @param  int  $index
     * @param  string|null  $default
     * @return string|null
     */
    public function segment($index, $default = null)
    {
        return Arr::get($this->segments(), $index - 1, $default);
    }

    /**
     * Get all of the segments for the request path.
	 * 获取请求路径的所有段
     *
     * @return array
     */
    public function segments()
    {
        $segments = explode('/', $this->decodedPath());

        return array_values(array_filter($segments, function ($value) {
            return $value !== '';
        }));
    }

    /**
     * Determine if the current request URI matches a pattern.
	 * 确定当前请求URI是否与模式匹配
     *
     * @param  mixed  ...$patterns
     * @return bool
     */
    public function is(...$patterns)
    {
        $path = $this->decodedPath();

        return collect($patterns)->contains(fn ($pattern) => Str::is($pattern, $path));
    }

    /**
     * Determine if the route name matches a given pattern.
	 * 确定路由名是否与给定模式匹配
     *
     * @param  mixed  ...$patterns
     * @return bool
     */
    public function routeIs(...$patterns)
    {
        return $this->route() && $this->route()->named(...$patterns);
    }

    /**
     * Determine if the current request URL and query string match a pattern.
	 * 确定当前请求URL和查询字符串是否与模式匹配
     *
     * @param  mixed  ...$patterns
     * @return bool
     */
    public function fullUrlIs(...$patterns)
    {
        $url = $this->fullUrl();

        return collect($patterns)->contains(fn ($pattern) => Str::is($pattern, $url));
    }

    /**
     * Get the host name.
	 * 获取主机名
     *
     * @return string
     */
    public function host()
    {
        return $this->getHost();
    }

    /**
     * Get the HTTP host being requested.
	 * 获取被请求的HTTP主机
     *
     * @return string
     */
    public function httpHost()
    {
        return $this->getHttpHost();
    }

    /**
     * Get the scheme and HTTP host.
	 * 获取模式和HTTP主机
     *
     * @return string
     */
    public function schemeAndHttpHost()
    {
        return $this->getSchemeAndHttpHost();
    }

    /**
     * Determine if the request is the result of an AJAX call.
	 * 确定请求是否是AJAX调用的结果
     *
     * @return bool
     */
    public function ajax()
    {
        return $this->isXmlHttpRequest();
    }

    /**
     * Determine if the request is the result of a PJAX call.
	 * 确定请求是否是PJAX调用的结果
     *
     * @return bool
     */
    public function pjax()
    {
        return $this->headers->get('X-PJAX') == true;
    }

    /**
     * Determine if the request is the result of a prefetch call.
	 * 确定请求是否是预取调用的结果
     *
     * @return bool
     */
    public function prefetch()
    {
        return strcasecmp($this->server->get('HTTP_X_MOZ') ?? '', 'prefetch') === 0 ||
               strcasecmp($this->headers->get('Purpose') ?? '', 'prefetch') === 0;
    }

    /**
     * Determine if the request is over HTTPS.
	 * 确定请求是否通过HTTPS
     *
     * @return bool
     */
    public function secure()
    {
        return $this->isSecure();
    }

    /**
     * Get the client IP address.
	 * 获取客户端IP地址
     *
     * @return string|null
     */
    public function ip()
    {
        return $this->getClientIp();
    }

    /**
     * Get the client IP addresses.
	 * 获取客户端IP地址
     *
     * @return array
     */
    public function ips()
    {
        return $this->getClientIps();
    }

    /**
     * Get the client user agent.
	 * 获取客户机用户代理
     *
     * @return string|null
     */
    public function userAgent()
    {
        return $this->headers->get('User-Agent');
    }

    /**
     * Merge new input into the current request's input array.
	 * 将新输入合并到当前请求的输入数组中
     *
     * @param  array  $input
     * @return $this
     */
    public function merge(array $input)
    {
        $this->getInputSource()->add($input);

        return $this;
    }

    /**
     * Merge new input into the request's input, but only when that key is missing from the request.
	 * 将新输入合并到请求的输入中，但仅当请求中缺少该键时。
     *
     * @param  array  $input
     * @return $this
     */
    public function mergeIfMissing(array $input)
    {
        return $this->merge(collect($input)->filter(function ($value, $key) {
            return $this->missing($key);
        })->toArray());
    }

    /**
     * Replace the input for the current request.
	 * 替换当前请求的输入
     *
     * @param  array  $input
     * @return $this
     */
    public function replace(array $input)
    {
        $this->getInputSource()->replace($input);

        return $this;
    }

    /**
     * This method belongs to Symfony HttpFoundation and is not usually needed when using Laravel.
	 * 这个方法属于Symfony HttpFoundation，在使用Laravel时通常不需要。
     *
     * Instead, you may use the "input" method.
	 * 相反，您可以使用"输入"方法。
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return parent::get($key, $default);
    }

    /**
     * Get the JSON payload for the request.
	 * 获取请求的JSON有效负载
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return \Symfony\Component\HttpFoundation\ParameterBag|mixed
     */
    public function json($key = null, $default = null)
    {
        if (! isset($this->json)) {
            $this->json = new InputBag((array) json_decode($this->getContent(), true));
        }

        if (is_null($key)) {
            return $this->json;
        }

        return data_get($this->json->all(), $key, $default);
    }

    /**
     * Get the input source for the request.
	 * 获取请求的输入源
     *
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    protected function getInputSource()
    {
        if ($this->isJson()) {
            return $this->json();
        }

        return in_array($this->getRealMethod(), ['GET', 'HEAD']) ? $this->query : $this->request;
    }

    /**
     * Create a new request instance from the given Laravel request.
	 * 从给定的Laravel请求创建一个新的请求实例
     *
     * @param  \Illuminate\Http\Request  $from
     * @param  \Illuminate\Http\Request|null  $to
     * @return static
     */
    public static function createFrom(self $from, $to = null)
    {
        $request = $to ?: new static;

        $files = array_filter($from->files->all());

        $request->initialize(
            $from->query->all(),
            $from->request->all(),
            $from->attributes->all(),
            $from->cookies->all(),
            $files,
            $from->server->all(),
            $from->getContent()
        );

        $request->headers->replace($from->headers->all());

        $request->setRequestLocale($from->getLocale());

        $request->setDefaultRequestLocale($from->getDefaultLocale());

        $request->setJson($from->json());

        if ($from->hasSession() && $session = $from->session()) {
            $request->setLaravelSession($session);
        }

        $request->setUserResolver($from->getUserResolver());

        $request->setRouteResolver($from->getRouteResolver());

        return $request;
    }

    /**
     * Create an Illuminate request from a Symfony instance.
	 * 从Symfony实例创建一个Illuminate请求
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @return static
     */
    public static function createFromBase(SymfonyRequest $request)
    {
        $newRequest = (new static)->duplicate(
            $request->query->all(), $request->request->all(), $request->attributes->all(),
            $request->cookies->all(), $request->files->all(), $request->server->all()
        );

        $newRequest->headers->replace($request->headers->all());

        $newRequest->content = $request->content;

        if ($newRequest->isJson()) {
            $newRequest->request = $newRequest->json();
        }

        return $newRequest;
    }

    /**
     * {@inheritdoc}
     *
     * @return static
     */
    public function duplicate(array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null): static
    {
        return parent::duplicate($query, $request, $attributes, $cookies, $this->filterFiles($files), $server);
    }

    /**
     * Filter the given array of files, removing any empty values.
	 * 过滤给定的文件数组，删除任何空值。
     *
     * @param  mixed  $files
     * @return mixed
     */
    protected function filterFiles($files)
    {
        if (! $files) {
            return;
        }

        foreach ($files as $key => $file) {
            if (is_array($file)) {
                $files[$key] = $this->filterFiles($files[$key]);
            }

            if (empty($files[$key])) {
                unset($files[$key]);
            }
        }

        return $files;
    }

    /**
     * {@inheritdoc}
     */
    public function hasSession(bool $skipIfUninitialized = false): bool
    {
        return ! is_null($this->session);
    }

    /**
     * {@inheritdoc}
     */
    public function getSession(): SessionInterface
    {
        return $this->hasSession()
                    ? new SymfonySessionDecorator($this->session())
                    : throw new SessionNotFoundException;
    }

    /**
     * Get the session associated with the request.
	 * 获取与请求关联的会话
     *
     * @return \Illuminate\Contracts\Session\Session
     *
     * @throws \RuntimeException
     */
    public function session()
    {
        if (! $this->hasSession()) {
            throw new RuntimeException('Session store not set on request.');
        }

        return $this->session;
    }

    /**
     * Set the session instance on the request.
	 * 在请求上设置会话实例
     *
     * @param  \Illuminate\Contracts\Session\Session  $session
     * @return void
     */
    public function setLaravelSession($session)
    {
        $this->session = $session;
    }

    /**
     * Set the locale for the request instance.
	 * 为请求实例设置语言环境
     *
     * @param  string  $locale
     * @return void
     */
    public function setRequestLocale(string $locale)
    {
        $this->locale = $locale;
    }

    /**
     * Set the default locale for the request instance.
	 * 为请求实例设置默认语言环境
     *
     * @param  string  $locale
     * @return void
     */
    public function setDefaultRequestLocale(string $locale)
    {
        $this->defaultLocale = $locale;
    }

    /**
     * Get the user making the request.
	 * 获取发出请求的用户
     *
     * @param  string|null  $guard
     * @return mixed
     */
    public function user($guard = null)
    {
        return call_user_func($this->getUserResolver(), $guard);
    }

    /**
     * Get the route handling the request.
	 * 获取处理请求的路由
     *
     * @param  string|null  $param
     * @param  mixed  $default
     * @return \Illuminate\Routing\Route|object|string|null
     */
    public function route($param = null, $default = null)
    {
        $route = call_user_func($this->getRouteResolver());

        if (is_null($route) || is_null($param)) {
            return $route;
        }

        return $route->parameter($param, $default);
    }

    /**
     * Get a unique fingerprint for the request / route / IP address.
	 * 获取请求/路由/ IP地址的唯一指纹
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function fingerprint()
    {
        if (! $route = $this->route()) {
            throw new RuntimeException('Unable to generate fingerprint. Route unavailable.');
        }

        return sha1(implode('|', array_merge(
            $route->methods(),
            [$route->getDomain(), $route->uri(), $this->ip()]
        )));
    }

    /**
     * Set the JSON payload for the request.
	 * 为请求设置JSON有效负载
     *
     * @param  \Symfony\Component\HttpFoundation\ParameterBag  $json
     * @return $this
     */
    public function setJson($json)
    {
        $this->json = $json;

        return $this;
    }

    /**
     * Get the user resolver callback.
	 * 获取用户解析器回调
     *
     * @return \Closure
     */
    public function getUserResolver()
    {
        return $this->userResolver ?: function () {
            //
        };
    }

    /**
     * Set the user resolver callback.
	 * 设置用户解析器回调
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function setUserResolver(Closure $callback)
    {
        $this->userResolver = $callback;

        return $this;
    }

    /**
     * Get the route resolver callback.
	 * 获取路由解析器回调
     *
     * @return \Closure
     */
    public function getRouteResolver()
    {
        return $this->routeResolver ?: function () {
            //
        };
    }

    /**
     * Set the route resolver callback.
	 * 设置路由解析器回调
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function setRouteResolver(Closure $callback)
    {
        $this->routeResolver = $callback;

        return $this;
    }

    /**
     * Get all of the input and files for the request.
	 * 获取请求的所有输入和文件
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->all();
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
        $route = $this->route();

        return Arr::has(
            $this->all() + ($route ? $route->parameters() : []),
            $offset
        );
    }

    /**
     * Get the value at the given offset.
	 * 获取给定偏移量处的值
     *
     * @param  string  $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->__get($offset);
    }

    /**
     * Set the value at the given offset.
	 * 在给定的偏移量处设置值
     *
     * @param  string  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->getInputSource()->set($offset, $value);
    }

    /**
     * Remove the value at the given offset.
	 * 移除给定偏移量处的值
     *
     * @param  string  $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        $this->getInputSource()->remove($offset);
    }

    /**
     * Check if an input element is set on the request.
	 * 检查请求上是否设置了输入元素
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return ! is_null($this->__get($key));
    }

    /**
     * Get an input element from the request.
	 * 从请求中获取输入元素
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return Arr::get($this->all(), $key, fn () => $this->route($key));
    }
}
