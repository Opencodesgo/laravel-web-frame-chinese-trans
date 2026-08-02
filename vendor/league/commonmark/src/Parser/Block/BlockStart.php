<?php
/**
 * League，CommonMark，解析器，块开始
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
use League\CommonMark\Parser\CursorState;

/**
 * Result object for starting parsing of a block; see static methods for constructors
 * 结果对象对一个块的开始解析;见构造函数的静态方法
 */
final class BlockStart
{
    /**
     * @var BlockContinueParserInterface[]
     *
     * @psalm-readonly
     */
    private array $blockParsers;

    /** @psalm-readonly-allow-private-mutation */
    private ?CursorState $cursorState = null;

    /** @psalm-readonly-allow-private-mutation */
    private bool $replaceActiveBlockParser = false;

    private bool $isAborting = false;

    private function __construct(BlockContinueParserInterface ...$blockParsers)
    {
        $this->blockParsers = $blockParsers;
    }

    /**
     * @return BlockContinueParserInterface[]
     */
    public function getBlockParsers(): iterable
    {
        return $this->blockParsers;
    }

    public function getCursorState(): ?CursorState
    {
        return $this->cursorState;
    }

    public function isReplaceActiveBlockParser(): bool
    {
        return $this->replaceActiveBlockParser;
    }

    /**
     * @internal
     */
    public function isAborting(): bool
    {
        return $this->isAborting;
    }

    /**
     * Signal that we want to parse at the given cursor position
	 * 我们要在给定的游标位置解析的信号
     *
     * @return $this
     */
    public function at(Cursor $cursor): self
    {
        $this->cursorState = $cursor->saveState();

        return $this;
    }

    /**
     * Signal that we want to replace the active block parser with this one
	 * 表示我们想用这个块解析器替换活动块解析器
     *
     * @return $this
     */
    public function replaceActiveBlockParser(): self
    {
        $this->replaceActiveBlockParser = true;

        return $this;
    }

    /**
     * Signal that we cannot parse whatever is here
	 * 表示我们无法解析这里的任何内容
     *
     * @return null
     */
    public static function none(): ?self
    {
        return null;
    }

    /**
     * Signal that we'd like to register the given parser(s) so they can parse the current block
	 * 表示我们想要注册给定的解析器，以便它们可以解析当前块
     */
    public static function of(BlockContinueParserInterface ...$blockParsers): self
    {
        return new self(...$blockParsers);
    }

    /**
     * Signal that the block parsing process should be aborted (no other block starts should be checked)
	 * 信号,该块解析过程应该被中止(不需要检查其他块开始)
     *
     * @internal
     */
    public static function abort(): self
    {
        $ret             = new self();
        $ret->isAborting = true;

        return $ret;
    }
}
