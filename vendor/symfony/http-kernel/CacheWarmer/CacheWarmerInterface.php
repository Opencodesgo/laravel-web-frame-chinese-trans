<?php
/**
 * Symfony，Component，HttpKernel，缓存取暖器，缓存取暖器接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\CacheWarmer;

/**
 * Interface for classes able to warm up the cache.
 * 接口，用于能够预热缓存的类。
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface CacheWarmerInterface extends WarmableInterface
{
    /**
     * Checks whether this warmer is optional or not.
	 * 检查加热器是否是可选的
     *
     * Optional warmers can be ignored on certain conditions.
	 * 可选的加热器在某些条件下可以忽略
     *
     * A warmer should return true if the cache can be
     * generated incrementally and on-demand.
     *
     * @return bool
     */
    public function isOptional();
}
