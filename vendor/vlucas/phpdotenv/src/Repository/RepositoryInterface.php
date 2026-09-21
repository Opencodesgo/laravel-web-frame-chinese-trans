<?php
/**
 * Dotenv，资源库，存储库接口 
 */

declare(strict_types=1);

namespace Dotenv\Repository;

interface RepositoryInterface
{
    /**
     * Determine if the given environment variable is defined.
	 * 确定是否定义了给定的环境变量
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name);

    /**
     * Get an environment variable.
	 * 获取一个环境变量
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return string|null
     */
    public function get(string $name);

    /**
     * Set an environment variable.
	 * 设置环境变量
     *
     * @param string $name
     * @param string $value
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    public function set(string $name, string $value);

    /**
     * Clear an environment variable.
	 * 清除一个环境变量
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    public function clear(string $name);
}
