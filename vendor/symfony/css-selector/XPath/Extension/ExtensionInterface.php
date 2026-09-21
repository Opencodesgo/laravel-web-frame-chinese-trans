<?php
/**
 * Symfony，Component，CssSelector，XPath，扩展，扩展接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\CssSelector\XPath\Extension;

/**
 * XPath expression translator extension interface.
 * XPath表达式转换器扩展接口。
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
interface ExtensionInterface
{
    /**
     * Returns node translators.
	 * 返回节点翻译器。
     *
     * These callables will receive the node as first argument and the translator as second argument.
     *
     * @return callable[]
     */
    public function getNodeTranslators(): array;

    /**
     * Returns combination translators.
	 * 返回组合翻译器
     *
     * @return callable[]
     */
    public function getCombinationTranslators(): array;

    /**
     * Returns function translators.
	 * 返回函数翻译器
     *
     * @return callable[]
     */
    public function getFunctionTranslators(): array;

    /**
     * Returns pseudo-class translators.
	 * 返回伪类翻译器。
     *
     * @return callable[]
     */
    public function getPseudoClassTranslators(): array;

    /**
     * Returns attribute operation translators.
	 * 返回属性操作转换器
     *
     * @return callable[]
     */
    public function getAttributeMatchingTranslators(): array;

    /**
     * Returns extension name.
	 * 返回扩展名
     */
    public function getName(): string;
}
