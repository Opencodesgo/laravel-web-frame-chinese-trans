<?php
/**
 * Symfony，Component，Translation，消息目录接口
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
 * 消息目录接口
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface MessageCatalogueInterface
{
    public const INTL_DOMAIN_SUFFIX = '+intl-icu';

    /**
     * Gets the catalogue locale.
	 * 获取目录区域设置
     */
    public function getLocale(): string;

    /**
     * Gets the domains.
	 * 获取域
     */
    public function getDomains(): array;

    /**
     * Gets the messages within a given domain.
	 * 获取给定域中的消息
     *
     * If $domain is null, it returns all messages.
     */
    public function all(?string $domain = null): array;

    /**
     * Sets a message translation.
	 * 设置消息转换
     *
     * @param string $id          The message id
     * @param string $translation The messages translation
     * @param string $domain      The domain name
     *
     * @return void
     */
    public function set(string $id, string $translation, string $domain = 'messages');

    /**
     * Checks if a message has a translation.
	 * 检查消息是否有翻译
     *
     * @param string $id     The message id
     * @param string $domain The domain name
     */
    public function has(string $id, string $domain = 'messages'): bool;

    /**
     * Checks if a message has a translation (it does not take into account the fallback mechanism).
	 * 检查消息是否有转换（它不考虑回退机制）
     *
     * @param string $id     The message id
     * @param string $domain The domain name
     */
    public function defines(string $id, string $domain = 'messages'): bool;

    /**
     * Gets a message translation.
	 * 获取消息翻译
     *
     * @param string $id     The message id
     * @param string $domain The domain name
     */
    public function get(string $id, string $domain = 'messages'): string;

    /**
     * Sets translations for a given domain.
	 * 设置给定域的翻译
     *
     * @param array  $messages An array of translations
     * @param string $domain   The domain name
     *
     * @return void
     */
    public function replace(array $messages, string $domain = 'messages');

    /**
     * Adds translations for a given domain.
	 * 添加给定域的翻译
     *
     * @param array  $messages An array of translations
     * @param string $domain   The domain name
     *
     * @return void
     */
    public function add(array $messages, string $domain = 'messages');

    /**
     * Merges translations from the given Catalogue into the current one.
	 * 将给定目录中的翻译合并到当前目录中
     *
     * The two catalogues must have the same locale.
     *
     * @return void
     */
    public function addCatalogue(self $catalogue);

    /**
     * Merges translations from the given Catalogue into the current one
     * only when the translation does not exist.
	 * 仅在当前目录中不存在翻译时，才将指定目录中的翻译合并到当前目录中。
     *
     * This is used to provide default translations when they do not exist for the current locale.
	 * 这用于在当前语言环境不存在默认翻译时提供默认翻译。
     *
     * @return void
     */
    public function addFallbackCatalogue(self $catalogue);

    /**
     * Gets the fallback catalogue.
	 * 获取备用目录
     */
    public function getFallbackCatalogue(): ?self;

    /**
     * Returns an array of resources loaded to build this collection.
	 * 返回为构建此集合而加载的资源数组
     *
     * @return ResourceInterface[]
     */
    public function getResources(): array;

    /**
     * Adds a resource for this collection.
	 * 为此集合添加资源
     *
     * @return void
     */
    public function addResource(ResourceInterface $resource);
}
