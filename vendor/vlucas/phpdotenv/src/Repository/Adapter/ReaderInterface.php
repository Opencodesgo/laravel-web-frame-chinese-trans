<?php
/**
 * Dotenv，资源库，适配器，读者接口
 */

declare(strict_types=1);

namespace Dotenv\Repository\Adapter;

interface ReaderInterface
{
    /**
     * Read an environment variable, if it exists.
	 * 读取环境变量（如果存在）
     *
     * @param non-empty-string $name
     *
     * @return \PhpOption\Option<string>
     */
    public function read(string $name);
}
