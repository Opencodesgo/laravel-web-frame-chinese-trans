<?php
/**
 * Dotenv，Repository，适配器，阅读器接口
 */

declare(strict_types=1);

namespace Dotenv\Repository\Adapter;

interface ReaderInterface
{
    /**
     * Read an environment variable, if it exists.
	 * 读取环境变量,如果存在的话
     *
     * @param non-empty-string $name
     *
     * @return \PhpOption\Option<string>
     */
    public function read(string $name);
}
