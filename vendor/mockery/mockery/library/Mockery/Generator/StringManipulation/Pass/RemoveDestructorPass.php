<?php
/**
 * Mockery，发生器，字符串操作，传递，移除析购传递
 */

/**
 * Mockery (https://docs.mockery.io/)
 *
 * @copyright https://github.com/mockery/mockery/blob/HEAD/COPYRIGHT.md
 * @license https://github.com/mockery/mockery/blob/HEAD/LICENSE BSD 3-Clause License
 * @link https://github.com/mockery/mockery for the canonical source repository
 */

namespace Mockery\Generator\StringManipulation\Pass;

use Mockery\Generator\MockConfiguration;
use function preg_replace;

/**
 * Remove mock's empty destructor if we tend to use original class destructor
 * 如果我们倾向于使用原始类析构函数,请删除mock的空析构函数。
 */
class RemoveDestructorPass implements Pass
{
    /**
     * @param  string $code
     * @return string
     */
    public function apply($code, MockConfiguration $config)
    {
        $target = $config->getTargetClass();

        if (! $target) {
            return $code;
        }

        if (! $config->isMockOriginalDestructor()) {
            return preg_replace('/public function __destruct\(\)\s+\{.*?\}/sm', '', $code);
        }

        return $code;
    }
}
