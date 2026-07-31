<?php
/**
 * Symfony，Component，HttpKernel，HTTP缓存，存储接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This code is partially based on the Rack-Cache library by Ryan Tomayko,
 * which is released under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\HttpCache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface implemented by HTTP cache stores.
 * 通过HTTP缓存存储实现的接口。
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface StoreInterface
{
    /**
     * Locates a cached Response for the Request provided.
	 * 为所提供的请求定位缓存响应
     *
     * @return Response|null
     */
    public function lookup(Request $request);

    /**
     * Writes a cache entry to the store for the given Request and Response.
	 * 为给定的请求和响应写入存储的缓存条目。
     *
     * Existing entries are read and any that match the response are removed. This
     * method calls write with the new list of cache entries.
     *
     * @return string The key under which the response is stored
     */
    public function write(Request $request, Response $response);

    /**
     * Invalidates all cache entries that match the request.
	 * 使所有缓存条目都无效,与请求相匹配
     */
    public function invalidate(Request $request);

    /**
     * Locks the cache for a given Request.
	 * 锁定给定请求的缓存
     *
     * @return bool|string true if the lock is acquired, the path to the current lock otherwise
     */
    public function lock(Request $request);

    /**
     * Releases the lock for the given Request.
	 * 释放给定请求的锁
     *
     * @return bool False if the lock file does not exist or cannot be unlocked, true otherwise
     */
    public function unlock(Request $request);

    /**
     * Returns whether or not a lock exists.
	 * 返回是否存在锁
     *
     * @return bool true if lock exists, false otherwise
     */
    public function isLocked(Request $request);

    /**
     * Purges data for the given URL.
	 * 清除给定URL的数据
     *
     * @return bool true if the URL exists and has been purged, false otherwise
     */
    public function purge(string $url);

    /**
     * Cleanups storage.
	 * 清理仓库
     */
    public function cleanup();
}
