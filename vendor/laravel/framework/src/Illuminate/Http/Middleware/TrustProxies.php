<?php
/**
 * Illuminate，Http，中间件，信任代理
 */

namespace Illuminate\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrustProxies
{
    /**
     * The trusted proxies for the application.
	 * 应用程序的可信代理
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * The proxy header mappings.
	 * 代理标头映射
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_PREFIX | Request::HEADER_X_FORWARDED_AWS_ELB;

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
    public function handle(Request $request, Closure $next)
    {
        $request::setTrustedProxies([], $this->getTrustedHeaderNames());

        $this->setTrustedProxyIpAddresses($request);

        return $next($request);
    }

    /**
     * Sets the trusted proxies on the request.
	 * 在请求上设置可信代理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function setTrustedProxyIpAddresses(Request $request)
    {
        $trustedIps = $this->proxies() ?: config('trustedproxy.proxies');

        if (is_null($trustedIps) && laravel_cloud()) {
            $trustedIps = '*';
        }

        if ($trustedIps === '*' || $trustedIps === '**') {
            return $this->setTrustedProxyIpAddressesToTheCallingIp($request);
        }

        $trustedIps = is_string($trustedIps)
                ? array_map('trim', explode(',', $trustedIps))
                : $trustedIps;

        if (is_array($trustedIps)) {
            return $this->setTrustedProxyIpAddressesToSpecificIps($request, $trustedIps);
        }
    }

    /**
     * Specify the IP addresses to trust explicitly.
	 * 明确指定要信任的IP地址
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $trustedIps
     * @return void
     */
    protected function setTrustedProxyIpAddressesToSpecificIps(Request $request, array $trustedIps)
    {
        $request->setTrustedProxies($trustedIps, $this->getTrustedHeaderNames());
    }

    /**
     * Set the trusted proxy to be the IP address calling this servers.
	 * 将可信代理设置为调用这些服务器的IP地址
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function setTrustedProxyIpAddressesToTheCallingIp(Request $request)
    {
        $request->setTrustedProxies([$request->server->get('REMOTE_ADDR')], $this->getTrustedHeaderNames());
    }

    /**
     * Retrieve trusted header name(s), falling back to defaults if config not set.
	 * 检索可信的报头名称，如果未设置配置，则返回默认值。
     *
     * @return int A bit field of Request::HEADER_*, to set which headers to trust from your proxies.
     */
    protected function getTrustedHeaderNames()
    {
        return match ($this->headers) {
            'HEADER_X_FORWARDED_AWS_ELB', Request::HEADER_X_FORWARDED_AWS_ELB => Request::HEADER_X_FORWARDED_AWS_ELB,
            'HEADER_FORWARDED', Request::HEADER_FORWARDED => Request::HEADER_FORWARDED,
            'HEADER_X_FORWARDED_FOR', Request::HEADER_X_FORWARDED_FOR => Request::HEADER_X_FORWARDED_FOR,
            'HEADER_X_FORWARDED_HOST', Request::HEADER_X_FORWARDED_HOST => Request::HEADER_X_FORWARDED_HOST,
            'HEADER_X_FORWARDED_PORT', Request::HEADER_X_FORWARDED_PORT => Request::HEADER_X_FORWARDED_PORT,
            'HEADER_X_FORWARDED_PROTO', Request::HEADER_X_FORWARDED_PROTO => Request::HEADER_X_FORWARDED_PROTO,
            'HEADER_X_FORWARDED_PREFIX', Request::HEADER_X_FORWARDED_PREFIX => Request::HEADER_X_FORWARDED_PREFIX,
            default => Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_PREFIX | Request::HEADER_X_FORWARDED_AWS_ELB,
        };
    }

    /**
     * Get the trusted proxies.
	 * 获取可信代理
     *
     * @return array|string|null
     */
    protected function proxies()
    {
        return $this->proxies;
    }
}
