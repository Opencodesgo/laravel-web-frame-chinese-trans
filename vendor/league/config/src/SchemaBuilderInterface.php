<?php
/**
 * League，Config，架构生成器接口
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

use Nette\Schema\Schema;

/**
 * Interface that allows new schemas to be added to a configuration
 * 接口,允许将新的模式添加到配置中
 */
interface SchemaBuilderInterface
{
    /**
     * Registers a new configuration schema at the given top-level key
	 * 在给定的顶层键注册一个新的配置模式
     */
    public function addSchema(string $key, Schema $schema): void;
}
