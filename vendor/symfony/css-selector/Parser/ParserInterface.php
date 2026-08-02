<?php
/**
 * Symfony，Component，CssSelector，分析程序，分析程序接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\CssSelector\Parser;

use Symfony\Component\CssSelector\Node\SelectorNode;

/**
 * CSS selector parser interface.
 * CSS选择器解析器接口。
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
interface ParserInterface
{
    /**
     * Parses given selector source into an array of tokens.
	 * 将选择器源指定为一个令牌数组
     *
     * @return SelectorNode[]
     */
    public function parse(string $source): array;
}
