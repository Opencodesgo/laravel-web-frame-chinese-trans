<?php declare(strict_types=1);

/**
 * SebastianBergmann，递归上下文，无效参数异常
 */

/*
 * This file is part of sebastian/recursion-context.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\RecursionContext;

final class InvalidArgumentException extends \InvalidArgumentException implements Exception
{
}
