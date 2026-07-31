<?php
/**
 * League，CommonMark，解析器，Markdown 解析器状态接口
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

use League\CommonMark\Parser\Block\BlockContinueParserInterface;

interface MarkdownParserStateInterface
{
    /**
     * Returns the deepest open block parser
	 * 返回最深的打开块解析器
     */
    public function getActiveBlockParser(): BlockContinueParserInterface;

    /**
     * Open block parser that was last matched during the continue phase. This is different from the currently active
     * block parser, as an unmatched block is only closed when a new block is started.
	 * 在继续阶段最后一次匹配的打开块解析器。
     */
    public function getLastMatchedBlockParser(): BlockContinueParserInterface;

    /**
     * Returns the current content of the paragraph if the matched block is a paragraph. The content can be multiple
     * lines separated by newlines.
	 * 如果匹配的块是一个段落，则返回该段的当前内容。
     */
    public function getParagraphContent(): ?string;
}
