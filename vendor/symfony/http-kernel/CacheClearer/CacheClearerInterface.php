<?php
/**
 * Symfony，Component，HttpKernel，缓存清除器，缓存清理接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\CacheClearer;

/**
 * CacheClearerInterface.
 * 缓存清理接口
 *
 * @author Dustin Dobervich <ddobervich@gmail.com>
 */
interface CacheClearerInterface
{
    /**
     * Clears any caches necessary.
	 * 清除任何必要的缓存
     *
     * @return void
     */
    public function clear(string $cacheDir);
}
