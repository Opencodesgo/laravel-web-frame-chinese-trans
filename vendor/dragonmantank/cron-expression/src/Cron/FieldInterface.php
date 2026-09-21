<?php
/**
 * Cron，字段接口
 */

declare(strict_types=1);

namespace Cron;

use DateTimeInterface;

/**
 * CRON field interface.
 */
interface FieldInterface
{
    /**
     * Check if the respective value of a DateTime field satisfies a CRON exp.
	 * 检查DateTime字段的相应值是否满足CRON exp
     *
     * @internal
     * @param DateTimeInterface $date  DateTime object to check
     * @param string            $value CRON expression to test against
     *
     * @return bool Returns TRUE if satisfied, FALSE otherwise
     */
    public function isSatisfiedBy(DateTimeInterface $date, $value, bool $invert): bool;

    /**
     * When a CRON expression is not satisfied, this method is used to increment
     * or decrement a DateTime object by the unit of the cron field.
	 * 当不满足CRON表达式时，使用此方法进行自增或者按cron字段的单位减去DateTime对象。
     *
     * @internal
     * @param DateTimeInterface $date DateTime object to change
     * @param bool $invert (optional) Set to TRUE to decrement
     * @param string|null $parts (optional) Set parts to use
     *
     * @return FieldInterface
     */
    public function increment(DateTimeInterface &$date, $invert = false, $parts = null): FieldInterface;

    /**
     * Validates a CRON expression for a given field.
	 * 验证给定字段的CRON表达式
     *
     * @param string $value CRON expression value to validate
     *
     * @return bool Returns TRUE if valid, FALSE otherwise
     */
    public function validate(string $value): bool;
}
