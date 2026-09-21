<?php
/**
 * Illuminate，契约，广播，广播接口
 */

namespace Illuminate\Contracts\Broadcasting;

interface Broadcaster
{
    /**
     * Authenticate the incoming request for a given channel.
	 * 验证给定信道的传入请求
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function auth($request);

    /**
     * Return the valid authentication response.
	 * 返回有效的身份验证响应
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $result
     * @return mixed
     */
    public function validAuthenticationResponse($request, $result);

    /**
     * Broadcast the given event.
	 * 广播给定事件
     *
     * @param  array  $channels
     * @param  string  $event
     * @param  array  $payload
     * @return void
     *
     * @throws \Illuminate\Broadcasting\BroadcastException
     */
    public function broadcast(array $channels, $event, array $payload = []);
}
