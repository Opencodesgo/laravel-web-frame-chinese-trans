<?php
/**
 * Symfony，Component，HttpKernel，片段，片段渲染器接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\Fragment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

/**
 * Interface implemented by all rendering strategies.
 * 由所有呈现策略实现的接口。
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface FragmentRendererInterface
{
    /**
     * Renders a URI and returns the Response content.
	 * 呈现一个URI并返回响应内容
     */
    public function render(string|ControllerReference $uri, Request $request, array $options = []): Response;

    /**
     * Gets the name of the strategy.
	 * 获取策略的名称
     */
    public function getName(): string;
}
