<?php
/**
 * Symfony，Component，HttpFoundation，请求堆栈
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Request stack that controls the lifecycle of requests.
 * 控制请求生命周期的请求堆栈。
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class RequestStack
{
    /**
     * @var Request[]
     */
    private array $requests = [];

    /**
     * Pushes a Request on the stack.
	 * 将请求推送到堆栈上。
     *
     * This method should generally not be called directly as the stack
     * management should be taken care of by the application itself.
     *
     * @return void
     */
    public function push(Request $request)
    {
        $this->requests[] = $request;
    }

    /**
     * Pops the current request from the stack.
	 * 从堆栈中弹出当前请求。
     *
     * This operation lets the current request go out of scope.
     *
     * This method should generally not be called directly as the stack
     * management should be taken care of by the application itself.
     */
    public function pop(): ?Request
    {
        if (!$this->requests) {
            return null;
        }

        return array_pop($this->requests);
    }

    public function getCurrentRequest(): ?Request
    {
        return end($this->requests) ?: null;
    }

    /**
     * Gets the main request.
	 * 获取主请求。
     *
     * Be warned that making your code aware of the main request
     * might make it un-compatible with other features of your framework
     * like ESI support.
     */
    public function getMainRequest(): ?Request
    {
        if (!$this->requests) {
            return null;
        }

        return $this->requests[0];
    }

    /**
     * Returns the parent request of the current.
	 * 对象的父请求。
     *
     * Be warned that making your code aware of the parent request
     * might make it un-compatible with other features of your framework
     * like ESI support.
     *
     * If current Request is the main request, it returns null.
     */
    public function getParentRequest(): ?Request
    {
        $pos = \count($this->requests) - 2;

        return $this->requests[$pos] ?? null;
    }

    /**
     * Gets the current session.
	 * 获取当前会话
     *
     * @throws SessionNotFoundException
     */
    public function getSession(): SessionInterface
    {
        if ((null !== $request = end($this->requests) ?: null) && $request->hasSession()) {
            return $request->getSession();
        }

        throw new SessionNotFoundException();
    }

    public function resetRequestFormats(): void
    {
        static $resetRequestFormats;
        $resetRequestFormats ??= \Closure::bind(static fn () => self::$formats = null, null, Request::class);
        $resetRequestFormats();
    }
}
