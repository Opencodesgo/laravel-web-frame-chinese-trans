<?php
/**
 * Mockery，匹配器，模式
 */

/**
 * Mockery (https://docs.mockery.io/)
 *
 * @copyright https://github.com/mockery/mockery/blob/HEAD/COPYRIGHT.md
 * @license https://github.com/mockery/mockery/blob/HEAD/LICENSE BSD 3-Clause License
 * @link https://github.com/mockery/mockery for the canonical source repository
 */

namespace Mockery\Matcher;

use function preg_match;

class Pattern extends MatcherAbstract
{
    /**
     * Return a string representation of this Matcher
	 * 返回此匹配器的字符串表示形式
     *
     * @return string
     */
    public function __toString()
    {
        return '<Pattern>';
    }

    /**
     * Check if the actual value matches the expected pattern.
	 * 检查实际值是否与期望的模式匹配
     *
     * @template TMixed
     *
     * @param TMixed $actual
     *
     * @return bool
     */
    public function match(&$actual)
    {
        return preg_match($this->_expected, (string) $actual) >= 1;
    }
}
