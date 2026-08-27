<?php
/**
 * League，Config，配置提供程序接口
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

namespace League\Config;

/**
 * Interface for a service which provides a readable configuration object
 * 用于提供可读配置对象的服务的接口
 */
interface ConfigurationProviderInterface
{
    public function getConfiguration(): ConfigurationInterface;
}
