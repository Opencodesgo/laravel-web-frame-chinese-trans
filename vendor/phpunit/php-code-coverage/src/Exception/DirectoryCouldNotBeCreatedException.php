<?php declare(strict_types=1);

/**
 * SebastianBergmann，CodeCoverage，工具，目录不能创建异常
 */

/*
 * This file is part of phpunit/php-code-coverage.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\CodeCoverage\Util;

use RuntimeException;
use SebastianBergmann\CodeCoverage\Exception;

final class DirectoryCouldNotBeCreatedException extends RuntimeException implements Exception
{
}
