<?php
/**
 * Dotenv，存储库，适配器，写入接口
 */

namespace Dotenv\Repository\Adapter;

interface WriterInterface extends AvailabilityInterface
{
    /**
     * Set an environment variable.
	 * 设置环境变量
     *
     * @param non-empty-string $name
     * @param string|null      $value
     *
     * @return void
     */
    public function set($name, $value = null);

    /**
     * Clear an environment variable.
     *
     * @param non-empty-string $name
     *
     * @return void
     */
    public function clear($name);
}
