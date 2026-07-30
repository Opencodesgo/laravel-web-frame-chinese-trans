<?php
/**
 * Mockery，匹配程序，Ducktype
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
	 * 返回此匹配器的字符串表示形式
     *
     * @return string
     */
    public function __toString()
    {
        return '<Ducktype[' . implode(', ', $this->_expected) . ']>';
    }

    /**
     * Check if the actual value matches the expected.
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
