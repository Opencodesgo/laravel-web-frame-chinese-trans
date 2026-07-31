<?php
/**
 * Mockery，匹配程序，有价值
 */

/**
 * Mockery (https://docs.mockery.io/)
 *
 * @copyright https://github.com/mockery/mockery/blob/HEAD/COPYRIGHT.md
 * @license https://github.com/mockery/mockery/blob/HEAD/LICENSE BSD 3-Clause License
 * @link https://github.com/mockery/mockery for the canonical source repository
 */

namespace Mockery\Matcher;

use ArrayAccess;

use function in_array;
use function is_array;

class HasValue extends MatcherAbstract
{
    /**
     * Return a string representation of this Matcher
	 * 返回这个Matcher的字符串表示
     *
     * @return string
     */
    public function __toString()
    {
        return '<HasValue[' . (string) $this->_expected . ']>';
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
        if (! is_array($actual) && ! $actual instanceof ArrayAccess) {
            return false;
        }

        return in_array($this->_expected, (array) $actual, true);
    }
}
