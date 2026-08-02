<?php
/**
 * Psy，超级全局 Env
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
 * Environment variables implementation via $_SERVER superglobal.
 * 通过$_SERVER superglobal实现环境变量。
 */
class SuperglobalsEnv implements EnvInterface
{
    /**
     * Get an environment variable by name.
     *
     * @return string|null
     */
    public function get(string $key)
    {
        if (isset($_SERVER[$key]) && $_SERVER[$key]) {
            return $_SERVER[$key];
        }

        return null;
    }
}
