<?php
/**
 * Symfony，Component，HttpFoundation，请求匹配器，路径请求匹配器
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\RequestMatcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

/**
 * Checks the Request URL path info matches a regular expression.
 * 检查请求URL路径信息是否匹配正则表达式
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class PathRequestMatcher implements RequestMatcherInterface
{
    public function __construct(private string $regexp)
    {
    }

    public function matches(Request $request): bool
    {
        return preg_match('{'.$this->regexp.'}', rawurldecode($request->getPathInfo()));
    }
}
