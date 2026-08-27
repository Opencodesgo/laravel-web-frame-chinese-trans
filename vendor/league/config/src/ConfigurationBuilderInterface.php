<?php
/**
 * League，Config，配置生成器界面
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
 * An interface that provides the ability to set both the schema and configuration values
 * 提供设置模式值和配置值的功能的接口
 */
interface ConfigurationBuilderInterface extends MutableConfigurationInterface, SchemaBuilderInterface
{
}
