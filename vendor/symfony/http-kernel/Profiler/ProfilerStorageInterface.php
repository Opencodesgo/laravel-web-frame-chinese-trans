<?php
/**
 * Symfony，Component，HttpKernel，分析器，文件分析器存储
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\Profiler;

/**
 * ProfilerStorageInterface.
 * 分析器存储接口
 *
 * This interface exists for historical reasons. The only supported
 * implementation is FileProfilerStorage.
 *
 * As the profiler must only be used on non-production servers, the file storage
 * is more than enough and no other implementations will ever be supported.
 *
 * @internal
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface ProfilerStorageInterface
{
    /**
     * Finds profiler tokens for the given criteria.
	 * 查找给定条件的分析器令牌
     *
     * @param int|null      $limit      The maximum number of tokens to return
     * @param int|null      $start      The start date to search from
     * @param int|null      $end        The end date to search to
     * @param string|null   $statusCode The response status code
     * @param \Closure|null $filter     A filter to apply on the list of tokens
     */
    public function find(?string $ip, ?string $url, ?int $limit, ?string $method, ?int $start = null, ?int $end = null/* , string $statusCode = null, \Closure $filter = null */): array;

    /**
     * Reads data associated with the given token.
	 * 读取与给定标记相关联的数据
     *
     * The method returns false if the token does not exist in the storage.
	 * 如果令牌不存在于存储中，则该方法返回false。
     */
    public function read(string $token): ?Profile;

    /**
     * Saves a Profile.
	 * 保存配置文件
     */
    public function write(Profile $profile): bool;

    /**
     * Purges all data from the database.
	 * 从数据库中清除所有数据
     *
     * @return void
     */
    public function purge();
}
