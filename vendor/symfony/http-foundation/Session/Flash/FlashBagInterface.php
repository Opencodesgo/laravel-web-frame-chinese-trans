<?php
/**
 * Symfony，Component，HttpFoundation，Session，闪存，闪存包接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\Session\Flash;

use Symfony\Component\HttpFoundation\Session\SessionBagInterface;

/**
 * FlashBagInterface.
 * 闪存包接口。
 *
 * @author Drak <drak@zikula.org>
 */
interface FlashBagInterface extends SessionBagInterface
{
    /**
     * Adds a flash message for the given type.
	 * 为给定类型添加flash消息
     *
     * @param mixed $message
     */
    public function add(string $type, $message);

    /**
     * Registers one or more messages for a given type.
	 * 为给定类型注册一条或多条消息
     *
     * @param string|array $messages
     */
    public function set(string $type, $messages);

    /**
     * Gets flash messages for a given type.
	 * 获取给定类型的flash消息
     *
     * @param string $type    Message category type
     * @param array  $default Default value if $type does not exist
     *
     * @return array
     */
    public function peek(string $type, array $default = []);

    /**
     * Gets all flash messages.
	 * 获取所有flash消息
     *
     * @return array
     */
    public function peekAll();

    /**
     * Gets and clears flash from the stack.
	 * 从堆栈中获取并清除flash
     *
     * @param array $default Default value if $type does not exist
     *
     * @return array
     */
    public function get(string $type, array $default = []);

    /**
     * Gets and clears flashes from the stack.
	 * 获取并清除堆栈中的闪烁
     *
     * @return array
     */
    public function all();

    /**
     * Sets all flash messages.
	 * 设置所有flash消息
     */
    public function setAll(array $messages);

    /**
     * Has flash messages for a given type?
	 * 有flash消息的给定类型
     *
     * @return bool
     */
    public function has(string $type);

    /**
     * Returns a list of all defined types.
	 * 返回所有已定义类型的列表
     *
     * @return array
     */
    public function keys();
}
