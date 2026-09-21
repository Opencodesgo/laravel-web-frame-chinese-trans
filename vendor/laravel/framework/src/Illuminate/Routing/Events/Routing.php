<?php
/**
 * Illuminate，路由，事件，路由选择
 */

namespace Illuminate\Routing\Events;

class Routing
{
    /**
     * The request instance.
	 * 请求实例
     *
     * @var \Illuminate\Http\Request
     */
    public $request;

    /**
     * Create a new event instance.
	 * 创建新的事件实例
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;
    }
}
