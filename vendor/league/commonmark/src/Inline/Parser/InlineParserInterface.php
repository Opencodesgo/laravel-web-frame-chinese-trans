<?php
/**
 * League，CommonMark，内联的，分析程序，内联解析器接口
 */

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark\Inline\Parser;

use League\CommonMark\InlineParserContext;

interface InlineParserInterface
{
    /**
     * @return string[]
     */
    public function getCharacters(): array;

    /**
     * @param InlineParserContext $inlineContext
     *
     * @return bool
     */
    public function parse(InlineParserContext $inlineContext): bool;
}
