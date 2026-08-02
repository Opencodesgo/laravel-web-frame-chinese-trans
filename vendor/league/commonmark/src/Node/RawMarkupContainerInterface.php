<?php
/**
 * League，CommonMark，节点，原始标记容器接口
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

namespace League\CommonMark\Node;

/**
 * Interface for a node which contains raw, unprocessed markup (like HTML)
 * 包含原始的、未处理的标记（如HTML）的节点的接口
 */
interface RawMarkupContainerInterface extends StringContainerInterface
{
}
