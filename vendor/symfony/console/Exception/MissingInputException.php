<?php
/**
 * Symfony，Component，Console，异常，缺失输入异常
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
 * Represents failure to read input from stdin.
 * 表示从stdin读取输入失败
 *
 * @author Gabriel Ostrolucký <gabriel.ostrolucky@gmail.com>
 */
class MissingInputException extends RuntimeException implements ExceptionInterface
{
}
