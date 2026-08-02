<?php
/**
 * Dotenv，Repository，适配器，写入接口
 */

declare(strict_types=1);

namespace Dotenv\Repository\Adapter;

interface WriterInterface
{
    /**
     * Write to an environment variable, if possible.
	 * 如果可能的话,写入环境变量
     *
     * @param non-empty-string $name
     * @param string           $value
     *
     * @return bool
     */
    public function write(string $name, string $value);

    /**
     * Delete an environment variable, if possible.
	 * 如果可能的话,删除一个环境变量
     *
     * @param non-empty-string $name
     *
     * @return bool
     */
    public function delete(string $name);
}
