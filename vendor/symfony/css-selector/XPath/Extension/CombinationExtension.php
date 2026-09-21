<?php
/**
 * Symfony，Component，CssSelector，XPath，扩展，组合扩展
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

use Symfony\Component\CssSelector\XPath\XPathExpr;

/**
 * XPath expression translator combination extension.
 * XPath表达式转换器组合扩展。
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class CombinationExtension extends AbstractExtension
{
    public function getCombinationTranslators(): array
    {
        return [
            ' ' => $this->translateDescendant(...),
            '>' => $this->translateChild(...),
            '+' => $this->translateDirectAdjacent(...),
            '~' => $this->translateIndirectAdjacent(...),
        ];
    }

    public function translateDescendant(XPathExpr $xpath, XPathExpr $combinedXpath): XPathExpr
    {
        return $xpath->join('/descendant-or-self::*/', $combinedXpath);
    }

    public function translateChild(XPathExpr $xpath, XPathExpr $combinedXpath): XPathExpr
    {
        return $xpath->join('/', $combinedXpath);
    }

    public function translateDirectAdjacent(XPathExpr $xpath, XPathExpr $combinedXpath): XPathExpr
    {
        return $xpath
            ->join('/following-sibling::', $combinedXpath)
            ->addNameTest()
            ->addCondition('position() = 1');
    }

    public function translateIndirectAdjacent(XPathExpr $xpath, XPathExpr $combinedXpath): XPathExpr
    {
        return $xpath->join('/following-sibling::', $combinedXpath);
    }

    public function getName(): string
    {
        return 'combination';
    }
}
