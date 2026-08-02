<?php
/**
 * App，Http，中间件，信任代理
 * 使用 Fideloper\Proxy\TrustProxies 扩展包
 */

namespace App\Http\Middleware;

use Fideloper\Proxy\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
	 * 应用的可信任代理
     *
     * @var array|string|null
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
	 * 应该用于检测代理的头
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_ALL;
}
