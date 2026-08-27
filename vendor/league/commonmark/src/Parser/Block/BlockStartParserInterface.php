<?php
/**
 * League，CommonMark，解析器，代码块，块开始解析器接口
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

namespace League\CommonMark\Parser\Block;

use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;

/**
 * Interface for a block parser which identifies block starts.
 * 用于标识块开始的块解析器的接口。
 */
interface BlockStartParserInterface
{
    /**
     * Check whether we should handle the block at the current position
	 * 检查我们是否应该在当前位置处理阻塞
     *
     * @param Cursor                       $cursor      A cloned copy of the cursor at the current parsing location
     * @param MarkdownParserStateInterface $parserState Additional information about the state of the Markdown parser
     *
     * @return BlockStart|null The BlockStart that has been identified, or null if the block doesn't match here
     */
    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart;
}
