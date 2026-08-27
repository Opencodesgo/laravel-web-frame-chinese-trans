<?php
/**
 * Symfony，Component，Translation，目录元数据感知接口
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
 * This interface is used to get, set, and delete metadata about the Catalogue.
 * 接口功能获取、设置和删除目录的元数据。
 *
 * @author Hugo Alliaume <hugo@alliau.me>
 */
interface CatalogueMetadataAwareInterface
{
    /**
     * Gets catalogue metadata for the given domain and key.
	 * 获取给定域和键的目录元数据。
     *
     * Passing an empty domain will return an array with all catalogue metadata indexed by
     * domain and then by key. Passing an empty key will return an array with all
     * catalogue metadata for the given domain.
	 * 传递一个空域名将返回一个数组，其中包含按域名和键索引的所有目录元数据。
	 * 传递一个空键将返回一个包含指定域名下所有目录元数据的数组。
     *
     * @return mixed The value that was set or an array with the domains/keys or null
     */
    public function getCatalogueMetadata(string $key = '', string $domain = 'messages'): mixed;

    /**
     * Adds catalogue metadata to a message domain.
	 * 将目录元数据添加到邮件域
     *
     * @return void
     */
    public function setCatalogueMetadata(string $key, mixed $value, string $domain = 'messages');

    /**
     * Deletes catalogue metadata for the given key and domain.
	 * 删除给定键和域的目录元数据。
     *
     * Passing an empty domain will delete all catalogue metadata. Passing an empty key will
     * delete all metadata for the given domain.
	 * 传递空域名将删除该目录的所有元数据。传递空密钥将删除指定域名的所有元数据。
     *
     * @return void
     */
    public function deleteCatalogueMetadata(string $key = '', string $domain = 'messages');
}
