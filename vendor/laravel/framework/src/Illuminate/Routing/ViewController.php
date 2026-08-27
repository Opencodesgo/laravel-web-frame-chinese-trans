<?php
/**
 * Illuminate，路由，视图控制器
 */

namespace Illuminate\Routing;

use Illuminate\Contracts\Routing\ResponseFactory;

class ViewController extends Controller
{
    /**
     * The response factory implementation.
	 * 响应工厂实现
     *
     * @var \Illuminate\Contracts\Routing\ResponseFactory
     */
    protected $response;

    /**
     * Create a new controller instance.
	 * 创建一个新的控制器实例
     *
     * @param  \Illuminate\Contracts\Routing\ResponseFactory  $response
     * @return void
     */
    public function __construct(ResponseFactory $response)
    {
        $this->response = $response;
    }

    /**
     * Invoke the controller method.
	 * 调用控制器方法
     *
     * @param  mixed  ...$args
     * @return \Illuminate\Http\Response
     */
    public function __invoke(...$args)
    {
        $routeParameters = array_filter($args, function ($key) {
            return ! in_array($key, ['view', 'data', 'status', 'headers']);
        }, ARRAY_FILTER_USE_KEY);

        $args['data'] = array_merge($args['data'], $routeParameters);

        return $this->response->view(
            $args['view'],
            $args['data'],
            $args['status'],
            $args['headers']
        );
    }

    /**
     * Execute an action on the controller.
	 * 在控制器上执行一个操作
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function callAction($method, $parameters)
    {
        return $this->{$method}(...$parameters);
    }
}
