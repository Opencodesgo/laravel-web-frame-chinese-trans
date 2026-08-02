<?php
/**
 * Symfony，Component，HttpKernel，HTTP缓存，响应缓存策略接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This code is partially based on the Rack-Cache library by Ryan Tomayko,
 * which is released under the MIT license.
 * (based on commit 02d2b48d75bcb63cf1c0c7149c077ad256542801)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\HttpCache;

use Symfony\Component\HttpFoundation\Response;

/**
 * ResponseCacheStrategyInterface implementations know how to compute the
 * Response cache HTTP header based on the different response cache headers.
 * responsecachestrategy gyinterface实现知道如何计算基于不同响应缓存头的响应缓存HTTP头。
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface ResponseCacheStrategyInterface
{
    /**
     * Adds a Response.
	 * 添加一个响应
     */
    public function add(Response $response);

    /**
     * Updates the Response HTTP headers based on the embedded Responses.
	 * 根据嵌入式响应更新响应HTTP头
     */
    public function update(Response $response);
}
