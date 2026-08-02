<?php
/**
 * Symfony，Component，HttpFoundation，Session，储存，代理，抽象代理
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\Session\Storage\Proxy;

/**
 * @author Drak <drak@zikula.org>
 */
abstract class AbstractProxy
{
    /**
     * Flag if handler wraps an internal PHP session handler (using \SessionHandler).
	 * 标志如果处理程序包装内部PHP会话处理程序（使用\SessionHandler）
     *
     * @var bool
     */
    protected $wrapper = false;

    /**
     * @var string
     */
    protected $saveHandlerName;

    /**
     * Gets the session.save_handler name.
	 * 获取会话。save_handler名字。
     *
     * @return string|null
     */
    public function getSaveHandlerName()
    {
        return $this->saveHandlerName;
    }

    /**
     * Is this proxy handler and instance of \SessionHandlerInterface.
	 * 是这个代理处理程序和\SessionHandlerInterface的实例
     *
     * @return bool
     */
    public function isSessionHandlerInterface()
    {
        return $this instanceof \SessionHandlerInterface;
    }

    /**
     * Returns true if this handler wraps an internal PHP session save handler using \SessionHandler.
	 * 如果此处理程序使用\SessionHandler包装内部PHP会话保存处理程序，则返回true。
     *
     * @return bool
     */
    public function isWrapper()
    {
        return $this->wrapper;
    }

    /**
     * Has a session started?
	 * 会话开始了吗
     *
     * @return bool
     */
    public function isActive()
    {
        return \PHP_SESSION_ACTIVE === session_status();
    }

    /**
     * Gets the session ID.
	 * 获取会话ID
     *
     * @return string
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * Sets the session ID.
	 * 设置会话ID
     *
     * @throws \LogicException
     */
    public function setId(string $id)
    {
        if ($this->isActive()) {
            throw new \LogicException('Cannot change the ID of an active session.');
        }

        session_id($id);
    }

    /**
     * Gets the session name.
	 * 获取会话名称
     *
     * @return string
     */
    public function getName()
    {
        return session_name();
    }

    /**
     * Sets the session name.
	 * 设置会话名称
     *
     * @throws \LogicException
     */
    public function setName(string $name)
    {
        if ($this->isActive()) {
            throw new \LogicException('Cannot change the name of an active session.');
        }

        session_name($name);
    }
}
