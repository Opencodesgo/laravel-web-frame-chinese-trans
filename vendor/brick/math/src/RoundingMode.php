<?php
/**
 * Brick，Math，舍入模式
 */

declare(strict_types=1);

namespace Brick\Math;

/**
 * Specifies a rounding behavior for numerical operations capable of discarding precision.
 * 为能够丢弃精度的数值运算指定舍入行为。
 *
 * Each rounding mode indicates how the least significant returned digit of a rounded result
 * is to be calculated. If fewer digits are returned than the digits needed to represent the
 * exact numerical result, the discarded digits will be referred to as the discarded fraction
 * regardless the digits' contribution to the value of the number. In other words, considered
 * as a numerical value, the discarded fraction could have an absolute value greater than one.
 */
final class RoundingMode
{
    /**
     * Private constructor. This class is not instantiable.
	 * 私有构造函数。这个类不可实例化。
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Asserts that the requested operation has an exact result, hence no rounding is necessary.
	 * 断言所请求的操作有一个确切的结果，因此不需要舍入。
     *
     * If this rounding mode is specified on an operation that yields a result that
     * cannot be represented at the requested scale, a RoundingNecessaryException is thrown.
     */
    public const UNNECESSARY = 0;

    /**
     * Rounds away from zero.
	 * 四舍五入到零
     *
     * Always increments the digit prior to a nonzero discarded fraction.
     * Note that this rounding mode never decreases the magnitude of the calculated value.
     */
    public const UP = 1;

    /**
     * Rounds towards zero.
	 * 向零四舍五入
     *
     * Never increments the digit prior to a discarded fraction (i.e., truncates).
     * Note that this rounding mode never increases the magnitude of the calculated value.
     */
    public const DOWN = 2;

    /**
     * Rounds towards positive infinity.
	 * 趋于正无穷
     *
     * If the result is positive, behaves as for UP; if negative, behaves as for DOWN.
     * Note that this rounding mode never decreases the calculated value.
     */
    public const CEILING = 3;

    /**
     * Rounds towards negative infinity.
	 * 趋于负无穷
     *
     * If the result is positive, behave as for DOWN; if negative, behave as for UP.
     * Note that this rounding mode never increases the calculated value.
     */
    public const FLOOR = 4;

    /**
     * Rounds towards "nearest neighbor" unless both neighbors are equidistant, in which case round up.
	 * 向"最近邻居"舍入，除非两个邻居距离相等，在这种情况下，舍入。
     *
     * Behaves as for UP if the discarded fraction is >= 0.5; otherwise, behaves as for DOWN.
     * Note that this is the rounding mode commonly taught at school.
     */
    public const HALF_UP = 5;

    /**
     * Rounds towards "nearest neighbor" unless both neighbors are equidistant, in which case round down.
	 * 向"最近邻"舍入，除非两个近邻的距离相等，在这种情况下，舍入向下。
     *
     * Behaves as for UP if the discarded fraction is > 0.5; otherwise, behaves as for DOWN.
     */
    public const HALF_DOWN = 6;

    /**
     * Rounds towards "nearest neighbor" unless both neighbors are equidistant, in which case round towards positive infinity.
	 * 向“最近邻”四舍五入，除非两个近邻的距离相等，在这种情况下，四舍五入到正无穷。
     *
     * If the result is positive, behaves as for HALF_UP; if negative, behaves as for HALF_DOWN.
     */
    public const HALF_CEILING = 7;

    /**
     * Rounds towards "nearest neighbor" unless both neighbors are equidistant, in which case round towards negative infinity.
	 * 向“最近邻”四舍五入，除非两个近邻的距离相等，在这种情况下，向负无穷四舍五入。
     *
     * If the result is positive, behaves as for HALF_DOWN; if negative, behaves as for HALF_UP.
     */
    public const HALF_FLOOR = 8;

    /**
     * Rounds towards the "nearest neighbor" unless both neighbors are equidistant, in which case rounds towards the even neighbor.
	 * 向"最近邻居"舍入，除非两个邻居是等距的，在这种情况下，向偶数邻居舍入。
     *
     * Behaves as for HALF_UP if the digit to the left of the discarded fraction is odd;
     * behaves as for HALF_DOWN if it's even.
     *
     * Note that this is the rounding mode that statistically minimizes
     * cumulative error when applied repeatedly over a sequence of calculations.
     * It is sometimes known as "Banker's rounding", and is chiefly used in the USA.
     */
    public const HALF_EVEN = 9;
}
