<?php
/**
 * League，CommonMark，解析器，游标
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

use League\CommonMark\Exception\UnexpectedEncodingException;

class Cursor
{
    public const INDENT_LEVEL = 4;

    /** @psalm-readonly */
    private string $line;

    /** @psalm-readonly */
    private int $length;

    /**
     * @var int
     *
     * It's possible for this to be 1 char past the end, meaning we've parsed all chars and have
     * reached the end.  In this state, any character-returning method MUST return null.
	 * 这是可能的,因为它已经结束了,这意味着我们已经解析了所有的chars。
     */
    private int $currentPosition = 0;

    private int $column = 0;

    private int $indent = 0;

    private int $previousPosition = 0;

    private ?int $nextNonSpaceCache = null;

    private bool $partiallyConsumedTab = false;

    /**
     * @var int|false
     *
     * @psalm-readonly
     */
    private $lastTabPosition;

    /** @psalm-readonly */
    private bool $isMultibyte;

    /** @var array<int, string> */
    private array $charCache = [];

    /**
     * @param string $line The line being parsed (ASCII or UTF-8)
     */
    public function __construct(string $line)
    {
        if (! \mb_check_encoding($line, 'UTF-8')) {
            throw new UnexpectedEncodingException('Unexpected encoding - UTF-8 or ASCII was expected');
        }

        $this->line            = $line;
        $this->length          = \mb_strlen($line, 'UTF-8') ?: 0;
        $this->isMultibyte     = $this->length !== \strlen($line);
        $this->lastTabPosition = $this->isMultibyte ? \mb_strrpos($line, "\t", 0, 'UTF-8') : \strrpos($line, "\t");
    }

    /**
     * Returns the position of the next character which is not a space (or tab)
	 * 返回下一个非空格（或制表符）字符的位置
     */
    public function getNextNonSpacePosition(): int
    {
        if ($this->nextNonSpaceCache !== null) {
            return $this->nextNonSpaceCache;
        }

        if ($this->currentPosition >= $this->length) {
            return $this->length;
        }

        $cols = $this->column;

        for ($i = $this->currentPosition; $i < $this->length; $i++) {
            // This if-else was copied out of getCharacter() for performance reasons
            if ($this->isMultibyte) {
                $c = $this->charCache[$i] ??= \mb_substr($this->line, $i, 1, 'UTF-8');
            } else {
                $c = $this->line[$i];
            }

            if ($c === ' ') {
                $cols++;
            } elseif ($c === "\t") {
                $cols += 4 - ($cols % 4);
            } else {
                break;
            }
        }

        $this->indent = $cols - $this->column;

        return $this->nextNonSpaceCache = $i;
    }

    /**
     * Returns the next character which isn't a space (or tab)
	 * 返回一个不是空格(或选项卡)的下一个字符
     */
    public function getNextNonSpaceCharacter(): ?string
    {
        $index = $this->getNextNonSpacePosition();
        if ($index >= $this->length) {
            return null;
        }

        if ($this->isMultibyte) {
            return $this->charCache[$index] ??= \mb_substr($this->line, $index, 1, 'UTF-8');
        }

        return $this->line[$index];
    }

    /**
     * Calculates the current indent (number of spaces after current position)
	 * 计算当前缩进(当前位置后空间的数量)
     */
    public function getIndent(): int
    {
        if ($this->nextNonSpaceCache === null) {
            $this->getNextNonSpacePosition();
        }

        return $this->indent;
    }

    /**
     * Whether the cursor is indented to INDENT_LEVEL
	 * 游标是否缩进INDENT_LEVEL
     */
    public function isIndented(): bool
    {
        if ($this->nextNonSpaceCache === null) {
            $this->getNextNonSpacePosition();
        }

        return $this->indent >= self::INDENT_LEVEL;
    }

    public function getCharacter(?int $index = null): ?string
    {
        if ($index === null) {
            $index = $this->currentPosition;
        }

        // Index out-of-bounds, or we're at the end
        if ($index < 0 || $index >= $this->length) {
            return null;
        }

        if ($this->isMultibyte) {
            return $this->charCache[$index] ??= \mb_substr($this->line, $index, 1, 'UTF-8');
        }

        return $this->line[$index];
    }

    /**
     * Slightly-optimized version of getCurrent(null)
     */
    public function getCurrentCharacter(): ?string
    {
        if ($this->currentPosition >= $this->length) {
            return null;
        }

        if ($this->isMultibyte) {
            return $this->charCache[$this->currentPosition] ??= \mb_substr($this->line, $this->currentPosition, 1, 'UTF-8');
        }

        return $this->line[$this->currentPosition];
    }

    /**
     * Returns the next character (or null, if none) without advancing forwards
	 * 返回下一个字符（如果没有，则为空），而不向前推进。
     */
    public function peek(int $offset = 1): ?string
    {
        return $this->getCharacter($this->currentPosition + $offset);
    }

    /**
     * Whether the remainder is blank
	 * 剩下的是空白的
     */
    public function isBlank(): bool
    {
        return $this->nextNonSpaceCache === $this->length || $this->getNextNonSpacePosition() === $this->length;
    }

    /**
     * Move the cursor forwards
     */
    public function advance(): void
    {
        $this->advanceBy(1);
    }

    /**
     * Move the cursor forwards
	 * 移动光标向前移动
     *
     * @param int  $characters       Number of characters to advance by
     * @param bool $advanceByColumns Whether to advance by columns instead of spaces
     */
    public function advanceBy(int $characters, bool $advanceByColumns = false): void
    {
        $this->previousPosition  = $this->currentPosition;
        $this->nextNonSpaceCache = null;

        if ($this->currentPosition >= $this->length || $characters === 0) {
            return;
        }

        // Optimization to avoid tab handling logic if we have no tabs
        if ($this->lastTabPosition === false || $this->currentPosition > $this->lastTabPosition) {
            $length                     = \min($characters, $this->length - $this->currentPosition);
            $this->partiallyConsumedTab = false;
            $this->currentPosition     += $length;
            $this->column              += $length;

            return;
        }

        $nextFewChars = $this->isMultibyte ?
            \mb_substr($this->line, $this->currentPosition, $characters, 'UTF-8') :
            \substr($this->line, $this->currentPosition, $characters);

        if ($characters === 1) {
            $asArray = [$nextFewChars];
        } elseif ($this->isMultibyte) {
            /** @var string[] $asArray */
            $asArray = \mb_str_split($nextFewChars, 1, 'UTF-8');
        } else {
            $asArray = \str_split($nextFewChars);
        }

        foreach ($asArray as $c) {
            if ($c === "\t") {
                $charsToTab = 4 - ($this->column % 4);
                if ($advanceByColumns) {
                    $this->partiallyConsumedTab = $charsToTab > $characters;
                    $charsToAdvance             = $charsToTab > $characters ? $characters : $charsToTab;
                    $this->column              += $charsToAdvance;
                    $this->currentPosition     += $this->partiallyConsumedTab ? 0 : 1;
                    $characters                -= $charsToAdvance;
                } else {
                    $this->partiallyConsumedTab = false;
                    $this->column              += $charsToTab;
                    $this->currentPosition++;
                    $characters--;
                }
            } else {
                $this->partiallyConsumedTab = false;
                $this->currentPosition++;
                $this->column++;
                $characters--;
            }

            if ($characters <= 0) {
                break;
            }
        }
    }

    /**
     * Advances the cursor by a single space or tab, if present
	 * 将光标移动一个空格或制表符（如果存在）
     */
    public function advanceBySpaceOrTab(): bool
    {
        $character = $this->getCurrentCharacter();

        if ($character === ' ' || $character === "\t") {
            $this->advanceBy(1, true);

            return true;
        }

        return false;
    }

    /**
     * Parse zero or more space/tab characters
	 * 解析零或多个空格/标签字符
     *
     * @return int Number of positions moved
     */
    public function advanceToNextNonSpaceOrTab(): int
    {
        $newPosition = $this->nextNonSpaceCache ?? $this->getNextNonSpacePosition();
        if ($newPosition === $this->currentPosition) {
            return 0;
        }

        $this->advanceBy($newPosition - $this->currentPosition);
        $this->partiallyConsumedTab = false;

        // We've just advanced to where that non-space is,
        // so any subsequent calls to find the next one will
        // always return the current position.
        $this->nextNonSpaceCache = $this->currentPosition;
        $this->indent            = 0;

        return $this->currentPosition - $this->previousPosition;
    }

    /**
     * Parse zero or more space characters, including at most one newline.
	 * 解析零或更多的空格字符,包括最多一条新线。
     *
     * Tab characters are not parsed with this function.
     *
     * @return int Number of positions moved
     */
    public function advanceToNextNonSpaceOrNewline(): int
    {
        $currentCharacter = $this->getCurrentCharacter();

        // Optimization: Avoid the regex if we know there are no spaces or newlines
        if ($currentCharacter !== ' ' && $currentCharacter !== "\n") {
            $this->previousPosition = $this->currentPosition;

            return 0;
        }

        $matches = [];
        \preg_match('/^ *(?:\n *)?/', $this->getRemainder(), $matches, \PREG_OFFSET_CAPTURE);

        // [0][0] contains the matched text
        // [0][1] contains the index of that match
        \assert(isset($matches[0]));
        $increment = $matches[0][1] + \strlen($matches[0][0]);

        $this->advanceBy($increment);

        return $this->currentPosition - $this->previousPosition;
    }

    /**
     * Move the position to the very end of the line
	 * 把位置移动到直线的末端
     *
     * @return int The number of characters moved
     */
    public function advanceToEnd(): int
    {
        $this->previousPosition  = $this->currentPosition;
        $this->nextNonSpaceCache = null;

        $this->currentPosition = $this->length;

        return $this->currentPosition - $this->previousPosition;
    }

    public function getRemainder(): string
    {
        if ($this->currentPosition >= $this->length) {
            return '';
        }

        $prefix   = '';
        $position = $this->currentPosition;
        if ($this->partiallyConsumedTab) {
            $position++;
            $charsToTab = 4 - ($this->column % 4);
            $prefix     = \str_repeat(' ', $charsToTab);
        }

        $subString = $this->isMultibyte ?
            \mb_substr($this->line, $position, null, 'UTF-8') :
            \substr($this->line, $position);

        return $prefix . $subString;
    }

    public function getLine(): string
    {
        return $this->line;
    }

    public function isAtEnd(): bool
    {
        return $this->currentPosition >= $this->length;
    }

    /**
     * Try to match a regular expression
	 * 试着与正则表达式相匹配
     *
     * Returns the matching text and advances to the end of that match
     *
     * @psalm-param non-empty-string $regex
     */
    public function match(string $regex): ?string
    {
        $subject = $this->getRemainder();

        if (! \preg_match($regex, $subject, $matches, \PREG_OFFSET_CAPTURE)) {
            return null;
        }

        // $matches[0][0] contains the matched text
        // $matches[0][1] contains the index of that match

        if ($this->isMultibyte) {
            // PREG_OFFSET_CAPTURE always returns the byte offset, not the char offset, which is annoying
            $offset      = \mb_strlen(\substr($subject, 0, $matches[0][1]), 'UTF-8');
            $matchLength = \mb_strlen($matches[0][0], 'UTF-8');
        } else {
            $offset      = $matches[0][1];
            $matchLength = \strlen($matches[0][0]);
        }

        // [0][0] contains the matched text
        // [0][1] contains the index of that match
        $this->advanceBy($offset + $matchLength);

        return $matches[0][0];
    }

    /**
     * Encapsulates the current state of this cursor in case you need to rollback later.
	 * 封装此游标的当前状态，以便以后需要回滚。
     *
     * WARNING: Do not parse or use the return value for ANYTHING except for
     * passing it back into restoreState(), as the number of values and their
     * contents may change in any future release without warning.
     */
    public function saveState(): CursorState
    {
        return new CursorState([
            $this->currentPosition,
            $this->previousPosition,
            $this->nextNonSpaceCache,
            $this->indent,
            $this->column,
            $this->partiallyConsumedTab,
        ]);
    }

    /**
     * Restore the cursor to a previous state.
	 * 将光标恢复到以前的状态。
     *
     * Pass in the value previously obtained by calling saveState().
     */
    public function restoreState(CursorState $state): void
    {
        [
            $this->currentPosition,
            $this->previousPosition,
            $this->nextNonSpaceCache,
            $this->indent,
            $this->column,
            $this->partiallyConsumedTab,
        ] = $state->toArray();
    }

    public function getPosition(): int
    {
        return $this->currentPosition;
    }

    public function getPreviousText(): string
    {
        if ($this->isMultibyte) {
            return \mb_substr($this->line, $this->previousPosition, $this->currentPosition - $this->previousPosition, 'UTF-8');
        }

        return \substr($this->line, $this->previousPosition, $this->currentPosition - $this->previousPosition);
    }

    public function getSubstring(int $start, ?int $length = null): string
    {
        if ($this->isMultibyte) {
            return \mb_substr($this->line, $start, $length, 'UTF-8');
        }

        if ($length !== null) {
            return \substr($this->line, $start, $length);
        }

        return \substr($this->line, $start);
    }

    public function getColumn(): int
    {
        return $this->column;
    }
}
