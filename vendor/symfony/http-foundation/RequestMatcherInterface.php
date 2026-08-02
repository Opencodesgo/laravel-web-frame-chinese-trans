<?php
/**
 * Symfony，Component，HttpFoundation，请求匹配器接口
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

/**
 * RequestMatcherInterface is an interface for strategies to match a Request.
 * RequestMatcherInterface 是用于匹配请求的策略的接口
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface RequestMatcherInterface
{
    /**
     * Decides whether the rule(s) implemented by the strategy matches the supplied request.
	 * 决定策略实现的规则是否与提供的请求匹配
     *
     * @return bool
     */
    public function matches(Request $request);
}
