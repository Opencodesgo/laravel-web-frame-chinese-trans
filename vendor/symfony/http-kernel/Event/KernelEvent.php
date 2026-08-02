<?php
/**
 * Symfony，Component，HttpKernel，依赖注入，内核事件
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Base class for events dispatched in the HttpKernel component.
 * 在 HttpKernel 组件中调度的事件的基类
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class KernelEvent extends Event
{
    private $kernel;
    private $request;
    private $requestType;

    /**
     * @param int $requestType The request type the kernel is currently processing; one of
     *                         HttpKernelInterface::MAIN_REQUEST or HttpKernelInterface::SUB_REQUEST
     */
    public function __construct(HttpKernelInterface $kernel, Request $request, ?int $requestType)
    {
        $this->kernel = $kernel;
        $this->request = $request;
        $this->requestType = $requestType;
    }

    /**
     * Returns the kernel in which this event was thrown.
	 * 返回引发此事件的内核
     *
     * @return HttpKernelInterface
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    /**
     * Returns the request the kernel is currently processing.
	 * 返回内核当前正在处理的请求
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns the request type the kernel is currently processing.
	 * 返回内核当前正在处理的请求类型
     *
     * @return int One of HttpKernelInterface::MAIN_REQUEST and
     *             HttpKernelInterface::SUB_REQUEST
     */
    public function getRequestType()
    {
        return $this->requestType;
    }

    /**
     * Checks if this is the main request.
	 * 检查这是否是主请求
     */
    public function isMainRequest(): bool
    {
        return HttpKernelInterface::MAIN_REQUEST === $this->requestType;
    }

    /**
     * Checks if this is a master request.
	 * 检查这是否是一个主请求
     *
     * @return bool
     *
     * @deprecated since symfony/http-kernel 5.3, use isMainRequest() instead
     */
    public function isMasterRequest()
    {
        trigger_deprecation('symfony/http-kernel', '5.3', '"%s()" is deprecated, use "isMainRequest()" instead.', __METHOD__);

        return $this->isMainRequest();
    }
}
