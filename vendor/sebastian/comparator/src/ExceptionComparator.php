<?php declare(strict_types=1);

/**
 * SebastianBergmann，比较器，异常比较器
 */

/*
 * This file is part of sebastian/comparator.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\Comparator;

use Exception;

/**
 * Compares Exception instances for equality.
 * 比较Exception实例是否相等。
 */
class ExceptionComparator extends ObjectComparator
{
    /**
     * Returns whether the comparator can compare two values.
	 * 返回比较器是否可以比较两个值
     *
     * @param mixed $expected The first value to compare
     * @param mixed $actual   The second value to compare
     *
     * @return bool
     */
    public function accepts($expected, $actual)
    {
        return $expected instanceof Exception && $actual instanceof Exception;
    }

    /**
     * Converts an object to an array containing all of its private, protected
     * and public properties.
	 * 将对象转换为一个包含其所有私有、受保护和公共属性的数组。
     *
     * @param object $object
     *
     * @return array
     */
    protected function toArray($object)
    {
        $array = parent::toArray($object);

        unset(
            $array['file'],
            $array['line'],
            $array['trace'],
            $array['string'],
            $array['xdebug_message']
        );

        return $array;
    }
}
