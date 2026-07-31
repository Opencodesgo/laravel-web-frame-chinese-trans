<?php
/**
 * League，CommonMark，渲染器，子节点渲染器接口
 */

declare(strict_types=1);

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark\Renderer;

use League\CommonMark\Node\Node;

/**
 * Renders multiple nodes by delegating to the individual node renderers and adding spacing where needed
 * 通过委托给单个节点渲染器并在需要的地方添加间距来呈现多个节点
 */
interface ChildNodeRendererInterface
{
    /**
     * @param Node[] $nodes
     */
    public function renderNodes(iterable $nodes): string;

    public function getBlockSeparator(): string;

    public function getInnerSeparator(): string;
}
