<?php
/**
 * League，CommonMark，解析器，内联解析器引擎接口
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

namespace League\CommonMark\Parser;

use League\CommonMark\Node\Block\AbstractBlock;

/**
 * Parser for inline content (text, links, emphasized text, etc).
 * 内联内容（文本，链接，强调文本等）的解析器。
 */
interface InlineParserEngineInterface
{
    /**
     * Parse the given contents as inlines and insert them into the given block
	 * 将给定的内容解析为内联，并将它们插入给定的块中。
     */
    public function parse(string $contents, AbstractBlock $block): void;
}
