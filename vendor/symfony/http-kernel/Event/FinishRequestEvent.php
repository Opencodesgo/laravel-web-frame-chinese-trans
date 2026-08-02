<?php
/**
 * Symfony，Component，HttpKernel，事件，完成请求事件
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\Event;

/**
 * Triggered whenever a request is fully processed.
 * 当请求被完全处理时触发
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
final class FinishRequestEvent extends KernelEvent
{
}
