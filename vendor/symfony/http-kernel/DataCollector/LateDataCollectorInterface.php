<?php
/**
 * Symfony，Component，HttpKernel，数据收集者，后期数据采集器接口
 *

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\DataCollector;

/**
 * LateDataCollectorInterface.
 * 后期数据采集器接口
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface LateDataCollectorInterface
{
    /**
     * Collects data as late as possible.
	 * 尽量晚收集数据
     *
     * @return void
     */
    public function lateCollect();
}
