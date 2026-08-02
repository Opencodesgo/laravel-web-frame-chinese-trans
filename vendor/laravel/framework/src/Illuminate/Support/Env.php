<?php
/**
 * Illuminate，支持，Env 环境
 */

namespace Illuminate\Support;

use Dotenv\Repository\Adapter\EnvConstAdapter;
use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\Adapter\ServerConstAdapter;
use Dotenv\Repository\RepositoryBuilder;
use PhpOption\Option;

class Env
{
    /**
     * Indicates if the putenv adapter is enabled.
	 * 显示putenv适配器是否已启用
     *
     * @var bool
     */
    protected static $putenv = true;

    /**
     * The environment repository instance.
	 * 环境存储库实例
     *
     * @var \Dotenv\Repository\RepositoryInterface|null
     */
    protected static $repository;

    /**
     * Enable the putenv adapter.
	 * 启用putenv适配器
     *
     * @return void
     */
    public static function enablePutenv()
    {
        static::$putenv = true;
        static::$repository = null;
    }

    /**
     * Disable the putenv adapter.
	 * 禁用putenv适配器
     *
     * @return void
     */
    public static function disablePutenv()
    {
        static::$putenv = false;
        static::$repository = null;
    }

    /**
     * Get the environment repository instance.
	 * 获取环境存储库实例
     *
     * @return \Dotenv\Repository\RepositoryInterface
     */
    public static function getRepository()
    {
        if (static::$repository === null) {
            $adapters = array_merge(
                [new EnvConstAdapter, new ServerConstAdapter],
                static::$putenv ? [new PutenvAdapter] : []
            );

            static::$repository = RepositoryBuilder::create()
                ->withReaders($adapters)
                ->withWriters($adapters)
                ->immutable()
                ->make();
        }

        return static::$repository;
    }

    /**
     * Gets the value of an environment variable.
	 * 获取环境变量的值
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return Option::fromValue(static::getRepository()->get($key))
            ->map(function ($value) {
                switch (strtolower($value)) {
                    case 'true':
                    case '(true)':
                        return true;
                    case 'false':
                    case '(false)':
                        return false;
                    case 'empty':
                    case '(empty)':
                        return '';
                    case 'null':
                    case '(null)':
                        return;
                }

                if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
                    return $matches[2];
                }

                return $value;
            })
            ->getOrCall(function () use ($default) {
                return value($default);
            });
    }
}
