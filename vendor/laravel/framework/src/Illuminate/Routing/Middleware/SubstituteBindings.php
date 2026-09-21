<?php
/**
 * Illuminate，路由，中间件，替代绑定
 */

namespace Illuminate\Routing\Middleware;

use Closure;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SubstituteBindings
{
    /**
     * The router instance.
	 * 路由器实例
     *
     * @var \Illuminate\Contracts\Routing\Registrar
     */
    protected $router;

    /**
     * Create a new bindings substitutor.
	 * 创建一个新的绑定替代器
     *
     * @param  \Illuminate\Contracts\Routing\Registrar  $router
     * @return void
     */
    public function __construct(Registrar $router)
    {
        $this->router = $router;
    }

    /**
     * Handle an incoming request.
	 * 处理传入请求
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $this->router->substituteBindings($route = $request->route());

            $this->router->substituteImplicitBindings($route);
        } catch (ModelNotFoundException $exception) {
            if ($route->getMissing()) {
                return $route->getMissing()($request, $exception);
            }

            throw $exception;
        }

        return $next($request);
    }
}
