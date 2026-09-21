<?php
/**
 * Symfony，Component，CssSelector，XPath，翻译器接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\CssSelector\XPath;

use Symfony\Component\CssSelector\Node\SelectorNode;

/**
 * XPath expression translator interface.
 * XPath表达式转换器接口。
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
interface TranslatorInterface
{
    /**
     * Translates a CSS selector to an XPath expression.
	 * 将CSS选择器转换为XPath表达式。
     */
    public function cssToXPath(string $cssExpr, string $prefix = 'descendant-or-self::'): string;

    /**
     * Translates a parsed selector node to an XPath expression.
	 * 将已解析的选择器节点转换为XPath表达式
     */
    public function selectorToXPath(SelectorNode $selector, string $prefix = 'descendant-or-self::'): string;
}
