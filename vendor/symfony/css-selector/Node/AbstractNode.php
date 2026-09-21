<?php
/**
 * Symfony，Component，CssSelector，节点，抽象节点
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\CssSelector\Node;

/**
 * Abstract base node class.
 * 抽象基节点类。
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
abstract class AbstractNode implements NodeInterface
{
    private string $nodeName;

    public function getNodeName(): string
    {
        return $this->nodeName ??= preg_replace('~.*\\\\([^\\\\]+)Node$~', '$1', static::class);
    }
}
