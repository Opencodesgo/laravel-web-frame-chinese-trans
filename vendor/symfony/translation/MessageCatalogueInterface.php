<?php
/**
 * Symfony，Component，Translation，信息目录接口
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

use Symfony\Component\Config\Resource\ResourceInterface;

/**
 * MessageCatalogueInterface.
 * 信息目录接口。
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface MessageCatalogueInterface
{
    public const INTL_DOMAIN_SUFFIX = '+intl-icu';

    /**
     * Gets the catalogue locale.
	 * 获取目录区域
     *
     * @return string
     */
    public function getLocale();

    /**
     * Gets the domains.
	 * 获取域
     *
     * @return array
     */
    public function getDomains();

    /**
     * Gets the messages within a given domain.
	 * 获取给定域内的消息。
     *
     * If $domain is null, it returns all messages.
     *
     * @param string|null $domain The domain name
     *
     * @return array
     */
    public function all(?string $domain = null);

    /**
     * Sets a message translation.
	 * 设置一个消息转换
     *
     * @param string $id          The message id
     * @param string $translation The messages translation
     * @param string $domain      The domain name
     */
    public function set(string $id, string $translation, string $domain = 'messages');

    /**
     * Checks if a message has a translation.
	 * 检查消息是否有翻译
     *
     * @param string $id     The message id
     * @param string $domain The domain name
     *
     * @return bool
     */
    public function has(string $id, string $domain = 'messages');

    /**
     * Checks if a message has a translation (it does not take into account the fallback mechanism).
	 * 检查消息是否有转换（它不考虑回退机制）
     *
     * @param string $id     The message id
     * @param string $domain The domain name
     *
     * @return bool
     */
    public function defines(string $id, string $domain = 'messages');

    /**
     * Gets a message translation.
	 * 获取消息翻译
     *
     * @param string $id     The message id
     * @param string $domain The domain name
     *
     * @return string
     */
    public function get(string $id, string $domain = 'messages');

    /**
     * Sets translations for a given domain.
	 * 设置给定域的翻译
     *
     * @param array  $messages An array of translations
     * @param string $domain   The domain name
     */
    public function replace(array $messages, string $domain = 'messages');

    /**
     * Adds translations for a given domain.
	 * 添加给定域的翻译
     *
     * @param array  $messages An array of translations
     * @param string $domain   The domain name
     */
    public function add(array $messages, string $domain = 'messages');

    /**
     * Merges translations from the given Catalogue into the current one.
	 * 将给定的目录的翻译合并到当前的目录中
     *
     * The two catalogues must have the same locale.
     */
    public function addCatalogue(self $catalogue);

    /**
     * Merges translations from the given Catalogue into the current one
     * only when the translation does not exist.
     *
     * This is used to provide default translations when they do not exist for the current locale.
     */
    public function addFallbackCatalogue(self $catalogue);

    /**
     * Gets the fallback catalogue.
	 * 获取回退目录
     *
     * @return self|null
     */
    public function getFallbackCatalogue();

    /**
     * Returns an array of resources loaded to build this collection.
	 * 返回加载到构建此集合的资源数组
     *
     * @return ResourceInterface[]
     */
    public function getResources();

    /**
     * Adds a resource for this collection.
	 * 为这个集合添加一个资源
     */
    public function addResource(ResourceInterface $resource);
}
