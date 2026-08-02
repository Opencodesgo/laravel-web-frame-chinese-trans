<?php
/**
 * Illuminate，基础，Http，中间件，维护期间的预防性请求
 */

namespace Illuminate\Foundation\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\MaintenanceModeBypassCookie;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PreventRequestsDuringMaintenance
{
    /**
     * The application implementation.
	 * 应用实现
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The URIs that should be accessible while maintenance mode is enabled.
	 * 在启用维护模式时应该可以访问的URI
     *
     * @var array
     */
    protected $except = [];

    /**
     * Create a new middleware instance.
	 * 创建新的中间件实例
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle an incoming request.
	 * 处理传入请求
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function handle($request, Closure $next)
    {
        if ($this->app->isDownForMaintenance()) {
            $data = json_decode(file_get_contents($this->app->storagePath().'/framework/down'), true);

            if (isset($data['secret']) && $request->path() === $data['secret']) {
                return $this->bypassResponse($data['secret']);
            }

            if ($this->hasValidBypassCookie($request, $data) ||
                $this->inExceptArray($request)) {
                return $next($request);
            }

            if (isset($data['redirect'])) {
                $path = $data['redirect'] === '/'
                            ? $data['redirect']
                            : trim($data['redirect'], '/');

                if ($request->path() !== $path) {
                    return redirect($path);
                }
            }

            if (isset($data['template'])) {
                return response(
                    $data['template'],
                    $data['status'] ?? 503,
                    $this->getHeaders($data)
                );
            }

            throw new HttpException(
                $data['status'] ?? 503,
                'Service Unavailable',
                null,
                $this->getHeaders($data)
            );
        }

        return $next($request);
    }

    /**
     * Determine if the incoming request has a maintenance mode bypass cookie.
	 * 确定传入请求是否具有维护模式绕过cookie
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $data
     * @return bool
     */
    protected function hasValidBypassCookie($request, array $data)
    {
        return isset($data['secret']) &&
                $request->cookie('laravel_maintenance') &&
                MaintenanceModeBypassCookie::isValid(
                    $request->cookie('laravel_maintenance'),
                    $data['secret']
                );
    }

    /**
     * Determine if the request has a URI that should be accessible in maintenance mode.
	 * 确定请求是否具有在维护模式下可访问的URI
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Redirect the user back to the root of the application with a maintenance mode bypass cookie.
	 * 使用维护模式旁路cookie将用户重定向到应用程序的根目录
     *
     * @param  string  $secret
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function bypassResponse(string $secret)
    {
        return redirect('/')->withCookie(
            MaintenanceModeBypassCookie::create($secret)
        );
    }

    /**
     * Get the headers that should be sent with the response.
	 * 获取应该随响应一起发送的标头
     *
     * @param  array  $data
     * @return array
     */
    protected function getHeaders($data)
    {
        $headers = isset($data['retry']) ? ['Retry-After' => $data['retry']] : [];

        if (isset($data['refresh'])) {
            $headers['Refresh'] = $data['refresh'];
        }

        return $headers;
    }

    /**
     * Get the URIs that should be accessible even when maintenance mode is enabled.
	 * 获取即使在启用维护模式时也应该可以访问的URI
     *
     * @return array
     */
    public function getExcludedPaths()
    {
        return $this->except;
    }
}
