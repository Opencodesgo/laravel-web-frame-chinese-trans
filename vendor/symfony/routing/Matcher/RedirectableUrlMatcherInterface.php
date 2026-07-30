<?php
/**
 * Symfony，Component，Routing，匹配程序，直接的 Url匹配程序接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Matcher;

/**
 * RedirectableUrlMatcherInterface knows how to redirect the user.
 * RedirectableUrlMatcherInterface知道如何重定向用户。
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface RedirectableUrlMatcherInterface
{
    /**
     * Redirects the user to another URL and returns the parameters for the redirection.
	 * 将用户重定向到另一个URL,并返回重定向的参数。
     *
     * @param string      $path   The path info to redirect to
     * @param string      $route  The route name that matched
     * @param string|null $scheme The URL scheme (null to keep the current one)
     *
     * @return array
     */
    public function redirect(string $path, string $route, ?string $scheme = null);
}
