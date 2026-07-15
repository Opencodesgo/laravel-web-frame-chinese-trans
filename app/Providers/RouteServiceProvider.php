<?php
/**
 * App，服务提供者，路由服务提供者
 */

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
	 * 这个名称空间应用于你的控制器路由
     *
     * In addition, it is set as the URL generator's root namespace.
	 * 另外，它被设置为URL生成器的根名称空间。
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * The path to the "home" route for your application.
	 * 应用的"home"路径
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
	 * 定义路由模型绑定
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
	 * 定义路由为应用程序
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
	 * 为应用定义web路由。
     *
     * These routes all receive session state, CSRF protection, etc.
	 * 这些路由都接收会话状态、CSRF保护等。
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
	 * 为应用定义api路由
     *
     * These routes are typically stateless.
	 * 这些路由通常是无状态的。
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }
}
