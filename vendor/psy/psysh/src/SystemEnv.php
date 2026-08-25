<?php
/**
 * Psy，系统 Env
 */

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2023 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy;

/**
 * Environment variables implementation via getenv().
 * 通过getenv（）实现环境变量。
 */
class SystemEnv implements EnvInterface
{
    /**
     * Get an environment variable by name.
	 * 按名称获取环境变量
     *
     * @return string|null
     */
    public function get(string $key)
    {
        if (isset($_SERVER[$key]) && $_SERVER[$key]) {
            return $_SERVER[$key];
        }

        $result = \getenv($key);

        return $result === false ? null : $result;
    }
}
