<?php
/**
 * Symfony，Component，Console，异常，无效选项异常
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Exception;

/**
 * Represents an incorrect option name or value typed in the console.
 * 在控制台中表示错误的选项名称或值。
 *
 * @author Jérôme Tamarelle <jerome@tamarelle.net>
 */
class InvalidOptionException extends \InvalidArgumentException implements ExceptionInterface
{
}
