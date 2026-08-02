<?php
/**
 * Symfony，Component，HttpKernel，依赖注入，查看事件
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

/**
 * Allows to create a response for the return value of a controller.
 * 允许为控制器的返回值创建响应
 *
 * Call setResponse() to set the response that will be returned for the
 * current request. The propagation of this event is stopped as soon as a
 * response is set.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
final class ViewEvent extends RequestEvent
{
    /**
     * The return value of the controller.
	 * 控制器的返回值
     *
     * @var mixed
     */
    private $controllerResult;

    public function __construct(HttpKernelInterface $kernel, Request $request, int $requestType, $controllerResult)
    {
        parent::__construct($kernel, $request, $requestType);

        $this->controllerResult = $controllerResult;
    }

    /**
     * Returns the return value of the controller.
	 * 返回控制器的返回值
     *
     * @return mixed
     */
    public function getControllerResult()
    {
        return $this->controllerResult;
    }

    /**
     * Assigns the return value of the controller.
	 * 分配控制器的返回值
     *
     * @param mixed $controllerResult The controller return value
     */
    public function setControllerResult($controllerResult): void
    {
        $this->controllerResult = $controllerResult;
    }
}
