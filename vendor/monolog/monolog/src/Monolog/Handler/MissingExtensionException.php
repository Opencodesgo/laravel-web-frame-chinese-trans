<?php declare(strict_types=1);

/**
 * Monolog，Handler，扩展丢失异常
 */

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
 * 如果缺少处理程序的扩展，则会引发异常。
 *
 * @author Christian Bergau <cbergau86@gmail.com>
 */
class MissingExtensionException extends \Exception
{
}
