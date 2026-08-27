<?php
/**
 * Carbon，异常，无效格式异常 
 */

/**
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Carbon\Exceptions;

use InvalidArgumentException as BaseInvalidArgumentException;

class InvalidFormatException extends BaseInvalidArgumentException implements InvalidArgumentException
{
    //
}
