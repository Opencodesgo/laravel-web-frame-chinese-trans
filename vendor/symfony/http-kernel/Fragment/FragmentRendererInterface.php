<?php
/**
 * Symfony，Component，HttpKernel，碎片，碎片渲染界面
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
 * 所有呈现策略实现的接口。
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface FragmentRendererInterface
{
    /**
     * Renders a URI and returns the Response content.
	 * 呈现URI并返回响应内容
     *
     * @param string|ControllerReference $uri A URI as a string or a ControllerReference instance
     *
     * @return Response
     */
    public function render($uri, Request $request, array $options = []);

    /**
     * Gets the name of the strategy.
	 * 获取策略的名称
     *
     * @return string
     */
    public function getName();
}
