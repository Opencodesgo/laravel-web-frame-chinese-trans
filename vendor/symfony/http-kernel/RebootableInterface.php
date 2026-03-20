<?php
/**
 * Symfony，Component，HttpKernel，Rebootable 接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel;

/**
 * Allows the Kernel to be rebooted using a temporary cache directory.
 * 允许使用临时缓存目录重新引导内核
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
interface RebootableInterface
{
    /**
     * Reboots a kernel.
	 * 重启内核
     *
     * The getBuildDir() method of a rebootable kernel should not be called
     * while building the container. Use the %kernel.build_dir% parameter instead.
     *
     * @param string|null $warmupDir pass null to reboot in the regular build directory
     */
    public function reboot(?string $warmupDir);
}
