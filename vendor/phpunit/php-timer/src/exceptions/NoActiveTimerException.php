<?php declare(strict_types=1);

/**
 * SebastianBergmann，计时器，无Active Timer异常
 */

/*
 * This file is part of phpunit/php-timer.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\Timer;

use LogicException;

final class NoActiveTimerException extends LogicException implements Exception
{
}
