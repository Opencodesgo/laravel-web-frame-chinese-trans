<?php
/**
 * League，CommonMark，Util，配置管理界面
 */

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark\Util;

interface ConfigurationInterface
{
    /**
     * @internal
     *
     * @deprecated
     */
    public const MISSING = '833f2700-af8d-49d4-9171-4b5f12d3bfbc';

    /**
     * Merge an existing array into the current configuration
	 * 将现有阵列合并到当前配置中
     *
     * @param array<string, mixed> $config
     *
     * @return void
     */
    public function merge(array $config = []);

    /**
     * Replace the entire array with something else
	 * 用其他东西替换整个数组
     *
     * @param array<string, mixed> $config
     *
     * @return void
     */
    public function replace(array $config = []);

    /**
     * Return the configuration value at the given key, or $default if no such config exists
	 * 返回给定键处的配置值，如果没有这样的配置，则返回$default。
     *
     * The key can be a string or a slash-delimited path to a nested value
     *
     * @param string|null $key
     * @param mixed|null  $default
     *
     * @return mixed|null
     */
    public function get(?string $key = null, $default = null);

    /**
     * Set the configuration value at the given key
	 * 在给定的键处设置配置值
     *
     * The key can be a string or a slash-delimited path to a nested value
	 * 键可以是字符串，也可以是指向嵌套值的斜杠分隔的路径。
     *
     * @param string     $key
     * @param mixed|null $value
     *
     * @return void
     */
    public function set(string $key, $value = null);
}
