<?php
/**
 * League，CommonMark，解析器，块，块继续解析器使用内联接口
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
	 * 解析当前块中的任何内线
     */
    public function parseInlines(InlineParserEngineInterface $inlineParser): void;
}
