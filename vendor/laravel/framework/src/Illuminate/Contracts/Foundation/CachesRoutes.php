<?php
/**
 * Illuminate，契约，基础，缓存路由
 */

namespace Illuminate\Contracts\Foundation;

interface CachesRoutes
{
    /**
     * Determine if the application routes are cached.
	 * 确定是否缓存了应用路由
     *
     * @return bool
     */
    public function routesAreCached();

    /**
     * Get the path to the routes cache file.
	 * 获取路由缓存文件的路径
     *
     * @return string
     */
    public function getCachedRoutesPath();
}
