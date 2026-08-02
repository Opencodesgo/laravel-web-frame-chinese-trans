<?php
/**
 * Symfony，Component，HttpKernel，片段，路由片段渲染器
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
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\EventListener\FragmentListener;

/**
 * Adds the possibility to generate a fragment URI for a given Controller.
 * 增加了为给定控制器生成片段URI的可能性
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class RoutableFragmentRenderer implements FragmentRendererInterface
{
    /**
     * @internal
     */
    protected $fragmentPath = '/_fragment';

    /**
     * Sets the fragment path that triggers the fragment listener.
	 * 设置触发片段侦听器的片段路径
     *
     * @see FragmentListener
     */
    public function setFragmentPath(string $path)
    {
        $this->fragmentPath = $path;
    }

    /**
     * Generates a fragment URI for a given controller.
	 * 为给定控制器生成片段 URI
     *
     * @param bool $absolute Whether to generate an absolute URL or not
     * @param bool $strict   Whether to allow non-scalar attributes or not
     *
     * @return string
     */
    protected function generateFragmentUri(ControllerReference $reference, Request $request, bool $absolute = false, bool $strict = true)
    {
        return (new FragmentUriGenerator($this->fragmentPath))->generate($reference, $request, $absolute, $strict, false);
    }
}
