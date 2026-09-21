<?php declare(strict_types=1);

/**
 * SebastianBergmann，Invoker，超时异常
 */

/*
 * This file is part of phpunit/php-invoker.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\Invoker;

use RuntimeException;

final class TimeoutException extends RuntimeException implements Exception
{
}
