<?php
/**
 * app，提供者，Route 路由服务提供者
 */

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
	 * 到应用程序"home"路由的路径
     *
     * This is used by Laravel authentication to redirect users after login.
	 * 这是Laravel认证在登录后重定向用户时使用的。
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * The controller namespace for the application.
	 * 应用程序的控制器命名空间
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
	 * 当存在时，控制器路由声明将自动使用此命名空间作为前缀。
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
	 * 配置应用程序速率限制器
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
