<?php
/**
 * Illuminate，路由，控制器，有中间件
 */

namespace Illuminate\Routing\Controllers;

interface HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
	 * 获取应该分配给控制器的中间件
     *
     * @return \Illuminate\Routing\Controllers\Middleware|array
     */
    public static function middleware();
}
