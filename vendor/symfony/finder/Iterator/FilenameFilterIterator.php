<?php
/**
 * Symfony，Component，Finder，迭代器，文件名过滤迭代器
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Finder\Iterator;

use Symfony\Component\Finder\Glob;

/**
 * FilenameFilterIterator filters files by patterns (a regexp, a glob, or a string).
 * FilenameFilterIterator按模式（regexp、glob或字符串）过滤文件
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @extends MultiplePcreFilterIterator<string, \SplFileInfo>
 */
class FilenameFilterIterator extends MultiplePcreFilterIterator
{
    /**
     * Filters the iterator values.
	 * 过滤迭代器值
     */
    public function accept(): bool
    {
        return $this->isAccepted($this->current()->getFilename());
    }

    /**
     * Converts glob to regexp.
	 * 将glob转换为regexp。
     *
     * PCRE patterns are left unchanged.
     * Glob strings are transformed with Glob::toRegex().
     *
     * @param string $str Pattern: glob or regexp
     */
    protected function toRegex(string $str): string
    {
        return $this->isRegex($str) ? $str : Glob::toRegex($str);
    }
}
