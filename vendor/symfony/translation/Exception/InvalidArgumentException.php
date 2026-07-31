<?php
/**
 * Symfony，Component，Translation，异常，无效参数异常
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Translation\Exception;

/**
 * Base InvalidArgumentException for the Translation component.
 * 对翻译组件的基础无效。
 *
 * @author Abdellatif Ait boudad <a.aitboudad@gmail.com>
 */
class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
}
