<?php
/**
 * Symfony，Component，HttpFoundation，会话，闪存，Flash Bag 接口
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
 * Flash Bag接口
 *
 * @author Drak <drak@zikula.org>
 */
interface FlashBagInterface extends SessionBagInterface
{
    /**
     * Adds a flash message for the given type.
	 * 为给定类型添加flash消息
     *
     * @return void
     */
    public function add(string $type, mixed $message);

    /**
     * Registers one or more messages for a given type.
	 * 为给定类型注册一条或多条消息
     *
     * @return void
     */
    public function set(string $type, string|array $messages);

    /**
     * Gets flash messages for a given type.
	 * 获取给定类型的flash消息
     *
     * @param string $type    Message category type
     * @param array  $default Default value if $type does not exist
     */
    public function peek(string $type, array $default = []): array;

    /**
     * Gets all flash messages.
	 * 获取所有flash消息
     */
    public function peekAll(): array;

    /**
     * Gets and clears flash from the stack.
	 * 从堆栈中获取并清除flash。
     *
     * @param array $default Default value if $type does not exist
     */
    public function get(string $type, array $default = []): array;

    /**
     * Gets and clears flashes from the stack.
	 * 获取并清除堆栈中的闪烁
     */
    public function all(): array;

    /**
     * Sets all flash messages.
	 * 设置所有flash消息
     *
     * @return void
     */
    public function setAll(array $messages);

    /**
     * Has flash messages for a given type?
	 * 有flash消息的给定类型？
     */
    public function has(string $type): bool;

    /**
     * Returns a list of all defined types.
	 * 返回所有已定义类型的列表
     */
    public function keys(): array;
}
