<?php
/**
 * Doctrine，Instantiator，异常，异常接口
 */

declare(strict_types=1);

namespace Doctrine\Instantiator\Exception;

use Throwable;

/**
 * Base exception marker interface for the instantiator component
 * 实例化组件的基本异常标记接口
 */
interface ExceptionInterface extends Throwable
{
}
