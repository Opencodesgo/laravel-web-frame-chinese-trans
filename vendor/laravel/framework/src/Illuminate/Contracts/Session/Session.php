<?php
/**
 * Illuminate，契约，Session，Session会话
 */

namespace Illuminate\Contracts\Session;

interface Session
{
    /**
     * Get the name of the session.
	 * 获取会话的名称
     *
     * @return string
     */
    public function getName();

    /**
     * Get the current session ID.
	 * 获取当前会话ID
     *
     * @return string
     */
    public function getId();

    /**
     * Set the session ID.
	 * 设置会话ID
     *
     * @param  string  $id
     * @return void
     */
    public function setId($id);

    /**
     * Start the session, reading the data from a handler.
	 * 启动会话，从处理程序读取数据
     *
     * @return bool
     */
    public function start();

    /**
     * Save the session data to storage.
	 * 保存会话数据至存储中
     *
     * @return void
     */
    public function save();

    /**
     * Get all of the session data.
	 * 获取所有会话数据
     *
     * @return array
     */
    public function all();

    /**
     * Checks if a key exists.
	 * 检查是否存在密钥
     *
     * @param  string|array  $key
     * @return bool
     */
    public function exists($key);

    /**
     * Checks if a key is present and not null.
	 * 检查键是否存在且不为空
     *
     * @param  string|array  $key
     * @return bool
     */
    public function has($key);

    /**
     * Get an item from the session.
	 * 从会话中获取一个项
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Put a key / value pair or array of key / value pairs in the session.
	 * 在会话中放置键/值对或数组的键/值对
     *
     * @param  string|array  $key
     * @param  mixed  $value
     * @return void
     */
    public function put($key, $value = null);

    /**
     * Get the CSRF token value.
	 * 获取CSRF令牌值
     *
     * @return string
     */
    public function token();

    /**
     * Remove an item from the session, returning its value.
	 * 从会话中删除项，返回其值。
     *
     * @param  string  $key
     * @return mixed
     */
    public function remove($key);

    /**
     * Remove one or many items from the session.
	 * 移除一个或多个项目从会话中
     *
     * @param  string|array  $keys
     * @return void
     */
    public function forget($keys);

    /**
     * Remove all of the items from the session.
	 * 移除所有项目从会话中
     *
     * @return void
     */
    public function flush();

    /**
     * Generate a new session ID for the session.
	 * 为会话生成一个新的会话ID
     *
     * @param  bool  $destroy
     * @return bool
     */
    public function migrate($destroy = false);

    /**
     * Determine if the session has been started.
	 * 确定会话是否已启动
     *
     * @return bool
     */
    public function isStarted();

    /**
     * Get the previous URL from the session.
	 * 从会话中获取前一个URL
     *
     * @return string|null
     */
    public function previousUrl();

    /**
     * Set the "previous" URL in the session.
	 * 设置会话中的"前一个"URL
     *
     * @param  string  $url
     * @return void
     */
    public function setPreviousUrl($url);

    /**
     * Get the session handler instance.
	 * 获取会话处理程序实例
     *
     * @return \SessionHandlerInterface
     */
    public function getHandler();

    /**
     * Determine if the session handler needs a request.
	 * 确定会话处理程序是否需要请求
     *
     * @return bool
     */
    public function handlerNeedsRequest();

    /**
     * Set the request on the handler instance.
	 * 在处理程序实例上设置请求
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function setRequestOnHandler($request);
}
