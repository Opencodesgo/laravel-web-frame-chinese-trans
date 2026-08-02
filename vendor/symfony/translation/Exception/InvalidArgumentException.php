<?php
/**
 * Symfony，组件，翻译，异常，无效参数异常
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
 * 翻译组件的基本InvalidArgumentException。
 *
 * @author Abdellatif Ait boudad <a.aitboudad@gmail.com>
 */
class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
}
