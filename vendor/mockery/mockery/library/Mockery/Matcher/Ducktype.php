<?php
/**
 * Mockery，匹配程序，Duck 类型
 */

/**
 * Mockery (https://docs.mockery.io/)
 *
 * @copyright https://github.com/mockery/mockery/blob/HEAD/COPYRIGHT.md
 * @license https://github.com/mockery/mockery/blob/HEAD/LICENSE BSD 3-Clause License
 * @link https://github.com/mockery/mockery for the canonical source repository
 */

namespace Mockery\Matcher;

use function implode;
use function is_object;
use function method_exists;

class Ducktype extends MatcherAbstract
{
    /**
     * Return a string representation of this Matcher
	 * 返回这个Matcher的字符串表示
     *
     * @return string
     */
    public function __toString()
    {
        return '<Ducktype[' . implode(', ', $this->_expected) . ']>';
    }

    /**
     * Check if the actual value matches the expected.
	 * 检查实际值是否符合预期
     *
     * @template TMixed
     *
     * @param TMixed $actual
     *
     * @return bool
     */
    public function match(&$actual)
    {
        if (! is_object($actual)) {
            return false;
        }

        foreach ($this->_expected as $method) {
            if (! method_exists($actual, $method)) {
                return false;
            }
        }

        return true;
    }
}
