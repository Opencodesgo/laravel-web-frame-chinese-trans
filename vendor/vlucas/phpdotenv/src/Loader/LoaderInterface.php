<?php
/**
 * Dotenv，加载器 ，加载器接口
 */

declare(strict_types=1);

namespace Dotenv\Loader;

use Dotenv\Repository\RepositoryInterface;

interface LoaderInterface
{
    /**
     * Load the given entries into the repository.
	 * 将给定的条目加载到存储库中
     *
     * @param \Dotenv\Repository\RepositoryInterface $repository
     * @param \Dotenv\Parser\Entry[]                 $entries
     *
     * @return array<string, string|null>
     */
    public function load(RepositoryInterface $repository, array $entries);
}
