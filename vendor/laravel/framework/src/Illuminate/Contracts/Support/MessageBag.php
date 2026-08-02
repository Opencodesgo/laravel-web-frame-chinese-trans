<?php
/**
 * Illuminate，契约，支持，信使包
 */

namespace Illuminate\Contracts\Support;

interface MessageBag extends Arrayable
{
    /**
     * Get the keys present in the message bag.
	 * 把密钥放在留言袋里
     *
     * @return array
     */
    public function keys();

    /**
     * Add a message to the bag.
	 * 在包中添加一条消息
     *
     * @param  string  $key
     * @param  string  $message
     * @return $this
     */
    public function add($key, $message);

    /**
     * Merge a new array of messages into the bag.
	 * 将一组新的消息合并到包中
     *
     * @param  \Illuminate\Contracts\Support\MessageProvider|array  $messages
     * @return $this
     */
    public function merge($messages);

    /**
     * Determine if messages exist for a given key.
	 * 确定是否存在给定键的消息
     *
     * @param  string|array  $key
     * @return bool
     */
    public function has($key);

    /**
     * Get the first message from the bag for a given key.
	 * 从包中获取给定键的第一条消息
     *
     * @param  string|null  $key
     * @param  string|null  $format
     * @return string
     */
    public function first($key = null, $format = null);

    /**
     * Get all of the messages from the bag for a given key.
	 * 从包中获取给定键的所有消息
     *
     * @param  string  $key
     * @param  string|null  $format
     * @return array
     */
    public function get($key, $format = null);

    /**
     * Get all of the messages for every key in the bag.
	 * 找到包里每把钥匙的所有信息
     *
     * @param  string|null  $format
     * @return array
     */
    public function all($format = null);

    /**
     * Get the raw messages in the container.
	 * 获取容器中的原始消息
     *
     * @return array
     */
    public function getMessages();

    /**
     * Get the default message format.
	 * 获取默认消息格式
     *
     * @return string
     */
    public function getFormat();

    /**
     * Set the default message format.
	 * 设置默认消息格式
     *
     * @param  string  $format
     * @return $this
     */
    public function setFormat($format = ':message');

    /**
     * Determine if the message bag has any messages.
	 * 确定消息包中是否有任何消息
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Determine if the message bag has any messages.
	 * 确定消息包中是否有任何消息
     *
     * @return bool
     */
    public function isNotEmpty();

    /**
     * Get the number of messages in the container.
	 * 获取容器中的消息数
     *
     * @return int
     */
    public function count();
}
