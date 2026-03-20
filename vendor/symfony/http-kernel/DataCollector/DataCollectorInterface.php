<?php
/**
 * Symfony，Component，HttpKernel，数据采集，数据采集器接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\ResetInterface;

/**
 * DataCollectorInterface.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface DataCollectorInterface extends ResetInterface
{
    /**
     * Collects data for the given Request and Response.
	 * 收集给定请求和响应的数据
     */
    public function collect(Request $request, Response $response, ?\Throwable $exception = null);

    /**
     * Returns the name of the collector.
	 * 返回收集器的名称
     *
     * @return string
     */
    public function getName();
}
