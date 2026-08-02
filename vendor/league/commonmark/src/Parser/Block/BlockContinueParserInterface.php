<?php
/**
 * League，CommonMark，解析器，块，块继续解析器接口
 */

declare(strict_types=1);

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (https://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark\Parser\Block;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Cursor;

/**
 * Interface for a block continuation parser
 * 一个块继续解析器的接口
 *
 * A block continue parser can only handle a single block instance. The current block being parsed is stored within this parser and
 * can be returned once parsing has completed. If you need to parse multiple block continuations, instantiate a new parser for each one.
 */
interface BlockContinueParserInterface
{
    /**
     * Return the current block being parsed by this parser
	 * 一个块继续解析器的接口
     */
    public function getBlock(): AbstractBlock;

    /**
     * Return whether we are parsing a container block
	 * 返回是否正在解析一个容器块
     */
    public function isContainer(): bool;

    /**
     * Return whether we are interested in possibly lazily parsing any subsequent lines
	 * 返回我们是否感兴趣可能延迟解析任何后续行
     */
    public function canHaveLazyContinuationLines(): bool;

    /**
     * Determine whether the current block being parsed can contain the given child block
	 * 确定当前块被解析是否可以包含给定的子块
     */
    public function canContain(AbstractBlock $childBlock): bool;

    /**
     * Attempt to parse the given line
     */
    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue;

    /**
     * Add the given line of text to the current block
	 * 一个块继续解析器的接口
     */
    public function addLine(string $line): void;

    /**
     * Close and finalize the current block
	 * 关闭并完成当前块
     */
    public function closeBlock(): void;
}
