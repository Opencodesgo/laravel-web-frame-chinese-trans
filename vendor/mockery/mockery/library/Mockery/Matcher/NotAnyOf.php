<?php
/**
 * Mockery，匹配程序，不任何的
 */

/**
 * Mockery (https://docs.mockery.io/)
 *
 * @copyright https://github.com/mockery/mockery/blob/HEAD/COPYRIGHT.md
 * @license https://github.com/mockery/mockery/blob/HEAD/LICENSE BSD 3-Clause License
 * @link https://github.com/mockery/mockery for the canonical source repository
 */

namespace Mockery\Matcher;

class NotAnyOf extends MatcherAbstract
{
    /**
     * Return a string representation of this Matcher
	 * 返回这个Matcher的字符串表示
     *
     * @return string
     */
    public function __toString()
    {
        return '<AnyOf>';
    }

    /**
     * Check if the actual value does not match the expected (in this
     * case it's specifically NOT expected).
	 * 检查实际值是否与预期不匹配(在此以防万一
     *
     * @template TMixed
     *
     * @param TMixed $actual
     *
     * @return bool
     */
    public function match(&$actual)
    {
        foreach ($this->_expected as $exp) {
            if ($actual === $exp || $actual == $exp) {
                return false;
            }
        }

        return true;
    }
}
