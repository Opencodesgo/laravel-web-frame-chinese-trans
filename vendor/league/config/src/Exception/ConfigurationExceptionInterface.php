<?php
/**
 * League，Config，异常，配置异常接口
 */

declare(strict_types=1);

/*
 * This file is part of the league/config package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Config\Exception;

/**
 * Marker interface for any/all exceptions thrown by this library
 * 该库抛出的任何/所有异常的标记接口
 */
interface ConfigurationExceptionInterface extends \Throwable
{
}
