<?php
/**
 * Faker，计算器，Luhn
 */

namespace Faker\Calculator;

/**
 * Utility class for generating and validating Luhn numbers.
 * 用于生成和验证Luhn数的实用工具类。
 *
 * Luhn algorithm is used to validate credit card numbers, IMEI numbers, and
 * National Provider Identifier numbers.
 *
 * @see http://en.wikipedia.org/wiki/Luhn_algorithm
 */
class Luhn
{
    /**
     * @return int
     */
    private static function checksum(string $number)
    {
        $number = (string) $number;
        $length = strlen($number);
        $sum = 0;

        for ($i = $length - 1; $i >= 0; $i -= 2) {
            $sum += $number[$i];
        }

        for ($i = $length - 2; $i >= 0; $i -= 2) {
            $sum += array_sum(str_split($number[$i] * 2));
        }

        return $sum % 10;
    }

    /**
     * @return string
     */
    public static function computeCheckDigit(string $partialNumber)
    {
        $checkDigit = self::checksum($partialNumber . '0');

        if ($checkDigit === 0) {
            return '0';
        }

        return (string) (10 - $checkDigit);
    }

    /**
     * Checks whether a number (partial number + check digit) is Luhn compliant
	 * 检查数字（部分数字+校验数字）是否符合Luhn标准
     *
     * @return bool
     */
    public static function isValid(string $number)
    {
        return self::checksum($number) === 0;
    }

    /**
     * Generate a Luhn compliant number.
	 * 生成一个符合Luhn标准的数字
     *
     * @return string
     */
    public static function generateLuhnNumber(string $partialValue)
    {
        if (!preg_match('/^\d+$/', $partialValue)) {
            throw new \InvalidArgumentException('Argument should be an integer.');
        }

        return $partialValue . Luhn::computeCheckDigit($partialValue);
    }
}
