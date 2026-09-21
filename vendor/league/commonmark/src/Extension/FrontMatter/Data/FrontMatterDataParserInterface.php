<?php
/**
 * League，CommonMark，扩展，前言，数据，前端问题数据解析器接口
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

namespace League\CommonMark\Extension\FrontMatter\Data;

use League\CommonMark\Extension\FrontMatter\Exception\InvalidFrontMatterException;

interface FrontMatterDataParserInterface
{
    /**
     * @return mixed|null The parsed data (which may be null, if the input represents a null value)
	 * 解析的数据（如果输入表示空值，则可能为空）
     *
     * @throws InvalidFrontMatterException if parsing fails
     */
    public function parse(string $frontMatter);
}
