<?php
/**
 * Dflydev，DotAccessData，异常，无效路径异常
 */

declare(strict_types=1);

/*
 * This file is a part of dflydev/dot-access-data.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dflydev\DotAccessData\Exception;

/**
 * Thrown when trying to access an invalid path in the data array
 * 试图访问数据数组中的无效路径时抛出
 */
class InvalidPathException extends DataException
{
}
