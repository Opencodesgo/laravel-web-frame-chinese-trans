<?php
/**
 * Symfony，Component，Translation，元数据感知接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Translation;

/**
 * MetadataAwareInterface.
 * 元数据感知接口
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface MetadataAwareInterface
{
    /**
     * Gets metadata for the given domain and key.
	 * 获取给定域和键的元数据
     *
     * Passing an empty domain will return an array with all metadata indexed by
     * domain and then by key. Passing an empty key will return an array with all
     * metadata for the given domain.
     *
     * @return mixed The value that was set or an array with the domains/keys or null
     */
    public function getMetadata(string $key = '', string $domain = 'messages');

    /**
     * Adds metadata to a message domain.
	 * 将元数据添加到消息域
     *
     * @param mixed $value
     */
    public function setMetadata(string $key, $value, string $domain = 'messages');

    /**
     * Deletes metadata for the given key and domain.
	 * 删除给定键和域的元数据
     *
     * Passing an empty domain will delete all metadata. Passing an empty key will
     * delete all metadata for the given domain.
     */
    public function deleteMetadata(string $key = '', string $domain = 'messages');
}
