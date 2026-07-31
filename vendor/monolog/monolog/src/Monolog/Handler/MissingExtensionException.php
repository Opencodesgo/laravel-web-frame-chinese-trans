<?php declare(strict_types=1);

/**
 * Monolog，处理程序，丢失扩展异常
 *

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Handler;

/**
 * Exception can be thrown if an extension for a handler is missing
 * 如果一个处理程序的扩展缺失,则可以抛出异常。
 *
 * @author Christian Bergau <cbergau86@gmail.com>
 */
class MissingExtensionException extends \Exception
{
}
