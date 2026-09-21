<?php
/**
 * Illuminate，契约，基础，缓存路由
 */

namespace Illuminate\Contracts\Foundation;

interface CachesRoutes
{
    /**
     * Determine if the application routes are cached.
	 * 确定是否应用路由被缓存
     *
     * @return bool
     */
    public function routesAreCached();

    /**
     * Get the path to the routes cache file.
	 * 得到路由缓存文件路径
     *
     * @return string
     */
    public function getCachedRoutesPath();
}
