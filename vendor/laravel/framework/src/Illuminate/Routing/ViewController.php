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
     * @param  array  $args
     * @return \Illuminate\Http\Response
     */
    public function __invoke(...$args)
    {
        [$view, $data, $status, $headers] = array_slice($args, -4);

        return $this->response->view($view, $data, $status, $headers);
    }
}
