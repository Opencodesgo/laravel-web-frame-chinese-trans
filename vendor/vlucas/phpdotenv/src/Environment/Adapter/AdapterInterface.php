<?php
/**
 * Dotenv，环境，适配器，适配器接口
 */

namespace Dotenv\Environment\Adapter;

interface AdapterInterface
{
    /**
     * Determines if the adapter is supported.
	 * 确定适配器是否被支持
     *
     * @return bool
     */
    public function isSupported();

    /**
     * Get an environment variable, if it exists.
     *
     * @param string $name
     *
     * @return \PhpOption\Option
     */
    public function get($name);

    /**
     * Set an environment variable.
     *
     * @param string      $name
     * @param string|null $value
     *
     * @return void
     */
    public function set($name, $value = null);

    /**
     * Clear an environment variable.
     *
     * @param string $name
     *
     * @return void
     */
    public function clear($name);
}
