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
 * 提供一个服务的接口,它提供可读的配置对象
 */
interface ConfigurationProviderInterface
{
    public function getConfiguration(): ConfigurationInterface;
}
