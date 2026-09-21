<?php
/**
 * Symfony，Component，Yaml，异常，运行时异常
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Yaml\Exception;

/**
 * Exception class thrown when an error occurs during parsing.
 * 在解析过程中发生错误时抛出的异常类。
 *
 * @author Romain Neutron <imprec@gmail.com>
 */
class RuntimeException extends \RuntimeException implements ExceptionInterface
{
}
