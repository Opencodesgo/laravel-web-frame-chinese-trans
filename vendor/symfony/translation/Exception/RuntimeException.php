<?php
/**
 * Symfony，组件，翻译，异常，运行时异常
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
 * Base RuntimeException for the Translation component.
 * 翻译组件的基本运行时异常。
 *
 * @author Abdellatif Ait boudad <a.aitboudad@gmail.com>
 */
class RuntimeException extends \RuntimeException implements ExceptionInterface
{
}
