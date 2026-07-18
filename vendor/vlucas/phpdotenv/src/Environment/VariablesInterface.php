<?php
/**
 * Dotenv，环境，变量接口
 */

namespace Dotenv\Environment;

use ArrayAccess;

/**
 * This environment variables interface.
 * 这个环境变量接口。
 */
interface VariablesInterface extends ArrayAccess
{
    /**
     * Determine if the environment is immutable.
	 * 确定环境是否不可变
     *
     * @return bool
     */
    public function isImmutable();

    /**
     * Tells whether environment variable has been defined.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name);

    /**
     * Get an environment variable.
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
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function clear($name);
}
