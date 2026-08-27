<?php
/**
 * League，CommonMark，解析器，代码块，带有内联接口的块继续解析器
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

use League\CommonMark\Parser\InlineParserEngineInterface;

interface BlockContinueParserWithInlinesInterface extends BlockContinueParserInterface
{
    /**
     * Parse any inlines inside of the current block
	 * 解析当前块中的所有内联
     */
    public function parseInlines(InlineParserEngineInterface $inlineParser): void;
}
