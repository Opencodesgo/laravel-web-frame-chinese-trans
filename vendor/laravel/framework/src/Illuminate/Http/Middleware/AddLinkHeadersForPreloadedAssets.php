<?php
/**
 * Illuminate，Http，中间件，为预加载资产添加链接头
 */

namespace Illuminate\Http\Middleware;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Vite;

class AddLinkHeadersForPreloadedAssets
{
    /**
     * Handle the incoming request.
	 * 处理传入请求
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle($request, $next)
    {
        return tap($next($request), function ($response) {
            if (Vite::preloadedAssets() !== []) {
                $response->header('Link', Collection::make(Vite::preloadedAssets())
                    ->map(fn ($attributes, $url) => "<{$url}>; ".implode('; ', $attributes))
                    ->join(', '));
            }
        });
    }
}
