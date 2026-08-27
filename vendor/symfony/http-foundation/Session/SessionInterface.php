<?php
/**
 * Symfony，Component，HttpFoundation，会话，会话接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\Session;

use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;

/**
 * Interface for the session.
 * 会话接口。
 *
 * @author Drak <drak@zikula.org>
 */
interface SessionInterface
{
    /**
     * Starts the session storage.
	 * 启动会话存储
     *
     * @throws \RuntimeException if session fails to start
     */
    public function start(): bool;

    /**
     * Returns the session ID.
	 * 返回会话ID
     */
    public function getId(): string;

    /**
     * Sets the session ID.
	 * 设置会话ID
     *
     * @return void
     */
    public function setId(string $id);

    /**
     * Returns the session name.
	 * 返回会话名称
     */
    public function getName(): string;

    /**
     * Sets the session name.
	 * 设置会话名称
     *
     * @return void
     */
    public function setName(string $name);

    /**
     * Invalidates the current session.
	 * 使当前会话无效。
     *
     * Clears all session attributes and flashes and regenerates the
     * session and deletes the old session from persistence.
     *
     * @param int|null $lifetime Sets the cookie lifetime for the session cookie. A null value
     *                           will leave the system settings unchanged, 0 sets the cookie
     *                           to expire with browser session. Time is in seconds, and is
     *                           not a Unix timestamp.
     */
    public function invalidate(?int $lifetime = null): bool;

    /**
     * Migrates the current session to a new session id while maintaining all
     * session attributes.
     *
     * @param bool     $destroy  Whether to delete the old session or leave it to garbage collection
     * @param int|null $lifetime Sets the cookie lifetime for the session cookie. A null value
     *                           will leave the system settings unchanged, 0 sets the cookie
     *                           to expire with browser session. Time is in seconds, and is
     *                           not a Unix timestamp.
     */
    public function migrate(bool $destroy = false, ?int $lifetime = null): bool;

    /**
     * Force the session to be saved and closed.
	 * 强制保存并关闭会话。
     *
     * This method is generally not required for real sessions as
     * the session will be automatically saved at the end of
     * code execution.
     *
     * @return void
     */
    public function save();

    /**
     * Checks if an attribute is defined.
	 * 检查是否定义了属性
     */
    public function has(string $name): bool;

    /**
     * Returns an attribute.
	 * 返回一个属性
     */
    public function get(string $name, mixed $default = null): mixed;

    /**
     * Sets an attribute.
	 * 设置属性
     *
     * @return void
     */
    public function set(string $name, mixed $value);

    /**
     * Returns attributes.
	 * 返回属性
     */
    public function all(): array;

    /**
     * Sets attributes.
	 * 设置属性
     *
     * @return void
     */
    public function replace(array $attributes);

    /**
     * Removes an attribute.
	 * 移除属性
     *
     * @return mixed The removed value or null when it does not exist
     */
    public function remove(string $name): mixed;

    /**
     * Clears all attributes.
	 * 清除所有属性
     *
     * @return void
     */
    public function clear();

    /**
     * Checks if the session was started.
	 * 检查会话是否已启动
     */
    public function isStarted(): bool;

    /**
     * Registers a SessionBagInterface with the session.
	 * 向会话注册一个SessionBagInterface
     *
     * @return void
     */
    public function registerBag(SessionBagInterface $bag);

    /**
     * Gets a bag instance by name.
	 * 按名称获取包实例
     */
    public function getBag(string $name): SessionBagInterface;

    /**
     * Gets session meta.
	 * 获取会话元数据
     */
    public function getMetadataBag(): MetadataBag;
}
