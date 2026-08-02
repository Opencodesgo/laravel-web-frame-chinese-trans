<?php
/**
 * PhpParser，碰撞，适配器，Laravel，检查员
 */

/**
 * This file is part of Collision.
 *
 * (c) Nuno Maduro <enunomaduro@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace NunoMaduro\Collision\Adapters\Laravel;

use Whoops\Exception\Inspector as BaseInspector;

/**
 * This is an Collision Laravel Adapter Inspector implementation.
 * 这是一个Collision Laravel Adapter Inspector的实现。
 *
 * @author Nuno Maduro <enunomaduro@gmail.com>
 */
class Inspector extends BaseInspector
{
    /**
     * {@inheritdoc}
     */
    protected function getTrace($e)
    {
        return $e->getTrace();
    }
}
