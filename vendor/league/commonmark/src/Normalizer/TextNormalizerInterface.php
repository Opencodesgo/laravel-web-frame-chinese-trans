<?php
/**
 * League，CommonMark，标准化者，文本标准化者
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

namespace League\CommonMark\Normalizer;

/**
 * Creates a normalized version of the given input text
 * 创建给定输入文本的规范化版本。
 */
interface TextNormalizerInterface
{
    /**
     * @param string $text    The text to normalize
     * @param mixed  $context Additional context about the text being normalized (optional)
     */
    public function normalize(string $text, $context = null): string;
}
