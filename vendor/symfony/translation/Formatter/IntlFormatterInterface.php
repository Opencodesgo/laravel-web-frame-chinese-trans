<?php
/**
 * Symfony，组件，翻译，格式化程序，Intl 格式化器接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Translation\Formatter;

/**
 * Formats ICU message patterns.
 * 格式化ICU消息模式。
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
interface IntlFormatterInterface
{
    /**
     * Formats a localized message using rules defined by ICU MessageFormat.
	 * 使用ICU MessageFormat定义的规则封装本地化消息
     *
     * @see http://icu-project.org/apiref/icu4c/classMessageFormat.html#details
     */
    public function formatIntl(string $message, string $locale, array $parameters = []): string;
}
