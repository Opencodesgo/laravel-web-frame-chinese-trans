<?php
/**
 * Dotenv，知识库，知识库接口
 */

namespace Dotenv\Repository;

use ArrayAccess;

/**
 * @extends \ArrayAccess<string,string|null>
 */
interface RepositoryInterface extends ArrayAccess
{
    /**
     * Tells whether environment variable has been defined.
	 * 说明是否已经定义了环境变量
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name);

    /**
     * Get an environment variable.
	 * 获取环境变量
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return string|null
     */
    public function get($name);

    /**
     * Set an environment variable.
	 * 设置环境变量
     *
     * @param string      $name
     * @param string|null $value
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function set($name, $value = null);

    /**
     * Clear an environment variable.
	 * 清除一个环境变量
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function clear($name);
}
