<?php
/**
 * Illuminate，契约，Session，会话
 */

namespace Illuminate\Contracts\Session;

interface Session
{
    /**
     * Get the name of the session.
	 * 得到会话名称
     *
     * @return string
     */
    public function getName();

    /**
     * Set the name of the session.
	 * 设置会话名称
     *
     * @param  string  $name
     * @return void
     */
    public function setName($name);

    /**
     * Get the current session ID.
	 * 得到当前会话ID
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
	 * 启动会话，从处理程序读取数据。
     *
     * @return bool
     */
    public function start();

    /**
     * Save the session data to storage.
	 * 将会话数据保存到存储中
     *
     * @return void
     */
    public function save();

    /**
     * Get all of the session data.
	 * 得到所有会话数据
     *
     * @return array
     */
    public function all();

    /**
     * Checks if a key exists.
	 * 检查键是否存在
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
	 * 从会话中获取一个项目
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Get the value of a given key and then forget it.
	 * 获取给定键的值，然后忘记它。
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function pull($key, $default = null);

    /**
     * Put a key / value pair or array of key / value pairs in the session.
	 * 在会话中放入一个键/值对或键/值对数组
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
     * Regenerate the CSRF token value.
	 * 重新生成CSRF令牌值
     *
     * @return void
     */
    public function regenerateToken();

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
	 * 从会话中删除一个或多个项目
     *
     * @param  string|array  $keys
     * @return void
     */
    public function forget($keys);

    /**
     * Remove all of the items from the session.
	 * 从会话中删除所有项
     *
     * @return void
     */
    public function flush();

    /**
     * Flush the session data and regenerate the ID.
	 * 刷新会话数据并重新生成ID
     *
     * @return bool
     */
    public function invalidate();

    /**
     * Generate a new session identifier.
	 * 生成一个新的会话标识符
     *
     * @param  bool  $destroy
     * @return bool
     */
    public function regenerate($destroy = false);

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
