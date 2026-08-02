<?php
/**
 * Dotenv，Repository，适配器，Putenv 适配器
 */

declare(strict_types=1);

namespace Dotenv\Repository\Adapter;

use PhpOption\None;
use PhpOption\Option;
use PhpOption\Some;

final class PutenvAdapter implements AdapterInterface
{
    /**
     * Create a new putenv adapter instance.
	 * 创建一个新的putenv适配器实例
     *
     * @return void
     */
    private function __construct()
    {
        //
    }

    /**
     * Create a new instance of the adapter, if it is available.
	 * 创建适配器的新实例（如果可用）
     *
     * @return \PhpOption\Option<\Dotenv\Repository\Adapter\AdapterInterface>
     */
    public static function create()
    {
        if (self::isSupported()) {
            /** @var \PhpOption\Option<AdapterInterface> */
            return Some::create(new self());
        }

        return None::create();
    }

    /**
     * Determines if the adapter is supported.
	 * 确定是否支持适配器
     *
     * @return bool
     */
    private static function isSupported()
    {
        return \function_exists('getenv') && \function_exists('putenv');
    }

    /**
     * Read an environment variable, if it exists.
	 * 读取环境变量（如果存在）
     *
     * @param non-empty-string $name
     *
     * @return \PhpOption\Option<string>
     */
    public function read(string $name)
    {
        /** @var \PhpOption\Option<string> */
        return Option::fromValue(\getenv($name), false)->filter(static function ($value) {
            return \is_string($value);
        });
    }

    /**
     * Write to an environment variable, if possible.
	 * 如果可能的话，写入环境变量。
     *
     * @param non-empty-string $name
     * @param string           $value
     *
     * @return bool
     */
    public function write(string $name, string $value)
    {
        \putenv("$name=$value");

        return true;
    }

    /**
     * Delete an environment variable, if possible.
	 * 如果可能，请删除环境变量。
     *
     * @param non-empty-string $name
     *
     * @return bool
     */
    public function delete(string $name)
    {
        \putenv($name);

        return true;
    }
}
