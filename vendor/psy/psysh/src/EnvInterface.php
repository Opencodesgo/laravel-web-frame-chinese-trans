<?php
/**
 * Psy，Env 接口
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
 * Abstraction around environment variables.
 * 围绕环境变量的抽象
 */
interface EnvInterface
{
    /**
     * Get an environment variable by name.
	 * 按名称获取环境变量
     *
     * @return string|null
     */
    public function get(string $key);
}
