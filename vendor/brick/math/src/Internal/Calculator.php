<?php
/**
 * Brick，Math，内部，计算器
 */

declare(strict_types=1);

namespace Brick\Math\Internal;

use Brick\Math\Exception\RoundingNecessaryException;
use Brick\Math\RoundingMode;

/**
 * Performs basic operations on arbitrary size integers.
 * 对任意大小的整数执行基本操作。
 *
 * Unless otherwise specified, all parameters must be validated as non-empty strings of digits,
 * without leading zero, and with an optional leading minus sign if the number is not zero.
 * 除非另有说明，否则所有参数必须验证为非空数字字符串。
 * 不带前导零，如果数字不为零，则带一个可选的前导减号。
 *
 * Any other parameter format will lead to undefined behaviour.
 * All methods must return strings respecting this format, unless specified otherwise.
 * 任何其他参数格式将导致未定义的行为。
 * 除非另有指定，否则所有方法必须返回符合此格式的字符串。
 *
 * @internal
 *
 * @psalm-immutable
 */
abstract class Calculator
{
    /**
     * The maximum exponent value allowed for the pow() method.
	 * pow()方法允许的最大指数值
     */
    public const MAX_POWER = 1000000;

    /**
     * The alphabet for converting from and to base 2 to 36, lowercase.
	 * 用于从基数2转换到基数36（小写）的字母表
     */
    public const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyz';

    /**
     * The Calculator instance in use.
	 * 正在使用的Calculator实例
     */
    private static ?Calculator $instance = null;

    /**
     * Sets the Calculator instance to use.
	 * 设置要使用的计算器实例
     *
     * An instance is typically set only in unit tests: the autodetect is usually the best option.
	 * 实例通常只在单元测试中设置：自动检测通常是最好的选择。
     *
     * @param Calculator|null $calculator The calculator instance, or NULL to revert to autodetect.
     */
    final public static function set(?Calculator $calculator) : void
    {
        self::$instance = $calculator;
    }

    /**
     * Returns the Calculator instance to use.
	 * 返回要使用的计算器实例
     *
     * If none has been explicitly set, the fastest available implementation will be returned.
	 * 如果没有显式设置，则返回最快的可用实现。
     *
     * @psalm-pure
     * @psalm-suppress ImpureStaticProperty
     */
    final public static function get() : Calculator
    {
        if (self::$instance === null) {
            /** @psalm-suppress ImpureMethodCall */
            self::$instance = self::detect();
        }

        return self::$instance;
    }

    /**
     * Returns the fastest available Calculator implementation.
	 * 返回最快的可用计算器实现。
     *
     * @codeCoverageIgnore
     */
    private static function detect() : Calculator
    {
        if (\extension_loaded('gmp')) {
            return new Calculator\GmpCalculator();
        }

        if (\extension_loaded('bcmath')) {
            return new Calculator\BcMathCalculator();
        }

        return new Calculator\NativeCalculator();
    }

    /**
     * Extracts the sign & digits of the operands.
	 * 提取操作数的符号和数字
     *
     * @return array{bool, bool, string, string} Whether $a and $b are negative, followed by their digits.
     */
    final protected function init(string $a, string $b) : array
    {
        return [
            $aNeg = ($a[0] === '-'),
            $bNeg = ($b[0] === '-'),

            $aNeg ? \substr($a, 1) : $a,
            $bNeg ? \substr($b, 1) : $b,
        ];
    }

    /**
     * Returns the absolute value of a number.
	 * 返回数字的绝对值
     */
    final public function abs(string $n) : string
    {
        return ($n[0] === '-') ? \substr($n, 1) : $n;
    }

    /**
     * Negates a number.
     */
    final public function neg(string $n) : string
    {
        if ($n === '0') {
            return '0';
        }

        if ($n[0] === '-') {
            return \substr($n, 1);
        }

        return '-' . $n;
    }

    /**
     * Compares two numbers.
	 * 比较两个数字
     *
     * @return int [-1, 0, 1] If the first number is less than, equal to, or greater than the second number.
     */
    final public function cmp(string $a, string $b) : int
    {
        [$aNeg, $bNeg, $aDig, $bDig] = $this->init($a, $b);

        if ($aNeg && ! $bNeg) {
            return -1;
        }

        if ($bNeg && ! $aNeg) {
            return 1;
        }

        $aLen = \strlen($aDig);
        $bLen = \strlen($bDig);

        if ($aLen < $bLen) {
            $result = -1;
        } elseif ($aLen > $bLen) {
            $result = 1;
        } else {
            $result = $aDig <=> $bDig;
        }

        return $aNeg ? -$result : $result;
    }

    /**
     * Adds two numbers.
	 * 两个数相加
     */
    abstract public function add(string $a, string $b) : string;

    /**
     * Subtracts two numbers.
	 * 两个数相减
     */
    abstract public function sub(string $a, string $b) : string;

    /**
     * Multiplies two numbers.
     */
    abstract public function mul(string $a, string $b) : string;

    /**
     * Returns the quotient of the division of two numbers.
     *
     * @param string $a The dividend.
     * @param string $b The divisor, must not be zero.
     *
     * @return string The quotient.
     */
    abstract public function divQ(string $a, string $b) : string;

    /**
     * Returns the remainder of the division of two numbers.
	 * 返回两个数的除法余数
     *
     * @param string $a The dividend.
     * @param string $b The divisor, must not be zero.
     *
     * @return string The remainder.
     */
    abstract public function divR(string $a, string $b) : string;

    /**
     * Returns the quotient and remainder of the division of two numbers.
	 * 返回两个数的除法的商和余数
     *
     * @param string $a The dividend.
     * @param string $b The divisor, must not be zero.
     *
     * @return array{string, string} An array containing the quotient and remainder.
     */
    abstract public function divQR(string $a, string $b) : array;

    /**
     * Exponentiates a number.
	 * 取一个数字的指数
     *
     * @param string $a The base number.
     * @param int    $e The exponent, validated as an integer between 0 and MAX_POWER.
     *
     * @return string The power.
     */
    abstract public function pow(string $a, int $e) : string;

    /**
     * @param string $b The modulus; must not be zero.
     */
    public function mod(string $a, string $b) : string
    {
        return $this->divR($this->add($this->divR($a, $b), $b), $b);
    }

    /**
     * Returns the modular multiplicative inverse of $x modulo $m.
	 * 返回$x模$m的模乘法逆
     *
     * If $x has no multiplicative inverse mod m, this method must return null.
	 * 如果$x没有对m取模的乘法逆，则此方法必须返回null。
     *
     * This method can be overridden by the concrete implementation if the underlying library has built-in support.
     *
     * @param string $m The modulus; must not be negative or zero.
     */
    public function modInverse(string $x, string $m) : ?string
    {
        if ($m === '1') {
            return '0';
        }

        $modVal = $x;

        if ($x[0] === '-' || ($this->cmp($this->abs($x), $m) >= 0)) {
            $modVal = $this->mod($x, $m);
        }

        [$g, $x] = $this->gcdExtended($modVal, $m);

        if ($g !== '1') {
            return null;
        }

        return $this->mod($this->add($this->mod($x, $m), $m), $m);
    }

    /**
     * Raises a number into power with modulo.
	 * 将一个数取模为幂
     *
     * @param string $base The base number; must be positive or zero.
     * @param string $exp  The exponent; must be positive or zero.
     * @param string $mod  The modulus; must be strictly positive.
     */
    abstract public function modPow(string $base, string $exp, string $mod) : string;

    /**
     * Returns the greatest common divisor of the two numbers.
	 * 返回两个数的最大公约数
     *
     * This method can be overridden by the concrete implementation if the underlying library
     * has built-in support for GCD calculations.
     *
     * @return string The GCD, always positive, or zero if both arguments are zero.
     */
    public function gcd(string $a, string $b) : string
    {
        if ($a === '0') {
            return $this->abs($b);
        }

        if ($b === '0') {
            return $this->abs($a);
        }

        return $this->gcd($b, $this->divR($a, $b));
    }

    /**
     * @return array{string, string, string} GCD, X, Y
     */
    private function gcdExtended(string $a, string $b) : array
    {
        if ($a === '0') {
            return [$b, '0', '1'];
        }

        [$gcd, $x1, $y1] = $this->gcdExtended($this->mod($b, $a), $a);

        $x = $this->sub($y1, $this->mul($this->divQ($b, $a), $x1));
        $y = $x1;

        return [$gcd, $x, $y];
    }

    /**
     * Returns the square root of the given number, rounded down.
	 * 返回给定数字的平方根，向下舍入。
     *
     * The result is the largest x such that x² ≤ n.
     * The input MUST NOT be negative.
     */
    abstract public function sqrt(string $n) : string;

    /**
     * Converts a number from an arbitrary base.
	 * 从任意进制转换数字
     *
     * This method can be overridden by the concrete implementation if the underlying library
     * has built-in support for base conversion.
     *
     * @param string $number The number, positive or zero, non-empty, case-insensitively validated for the given base.
     * @param int    $base   The base of the number, validated from 2 to 36.
     *
     * @return string The converted number, following the Calculator conventions.
     */
    public function fromBase(string $number, int $base) : string
    {
        return $this->fromArbitraryBase(\strtolower($number), self::ALPHABET, $base);
    }

    /**
     * Converts a number to an arbitrary base.
	 * 将数字转换为任意进制
     *
     * This method can be overridden by the concrete implementation if the underlying library
     * has built-in support for base conversion.
     *
     * @param string $number The number to convert, following the Calculator conventions.
     * @param int    $base   The base to convert to, validated from 2 to 36.
     *
     * @return string The converted number, lowercase.
     */
    public function toBase(string $number, int $base) : string
    {
        $negative = ($number[0] === '-');

        if ($negative) {
            $number = \substr($number, 1);
        }

        $number = $this->toArbitraryBase($number, self::ALPHABET, $base);

        if ($negative) {
            return '-' . $number;
        }

        return $number;
    }

    /**
     * Converts a non-negative number in an arbitrary base using a custom alphabet, to base 10.
	 * 使用自定义字母表将任意基数中的非负数转换为以10为基数
     *
     * @param string $number   The number to convert, validated as a non-empty string,
     *                         containing only chars in the given alphabet/base.
     * @param string $alphabet The alphabet that contains every digit, validated as 2 chars minimum.
     * @param int    $base     The base of the number, validated from 2 to alphabet length.
     *
     * @return string The number in base 10, following the Calculator conventions.
     */
    final public function fromArbitraryBase(string $number, string $alphabet, int $base) : string
    {
        // remove leading "zeros"
        $number = \ltrim($number, $alphabet[0]);

        if ($number === '') {
            return '0';
        }

        // optimize for "one"
        if ($number === $alphabet[1]) {
            return '1';
        }

        $result = '0';
        $power = '1';

        $base = (string) $base;

        for ($i = \strlen($number) - 1; $i >= 0; $i--) {
            $index = \strpos($alphabet, $number[$i]);

            if ($index !== 0) {
                $result = $this->add($result, ($index === 1)
                    ? $power
                    : $this->mul($power, (string) $index)
                );
            }

            if ($i !== 0) {
                $power = $this->mul($power, $base);
            }
        }

        return $result;
    }

    /**
     * Converts a non-negative number to an arbitrary base using a custom alphabet.
	 * 使用自定义字母表将非负数转换为任意基数
     *
     * @param string $number   The number to convert, positive or zero, following the Calculator conventions.
     * @param string $alphabet The alphabet that contains every digit, validated as 2 chars minimum.
     * @param int    $base     The base to convert to, validated from 2 to alphabet length.
     *
     * @return string The converted number in the given alphabet.
     */
    final public function toArbitraryBase(string $number, string $alphabet, int $base) : string
    {
        if ($number === '0') {
            return $alphabet[0];
        }

        $base = (string) $base;
        $result = '';

        while ($number !== '0') {
            [$number, $remainder] = $this->divQR($number, $base);
            $remainder = (int) $remainder;

            $result .= $alphabet[$remainder];
        }

        return \strrev($result);
    }

    /**
     * Performs a rounded division.
	 * 执行四舍五入除法
     *
     * Rounding is performed when the remainder of the division is not zero.
     *
     * @param string $a            The dividend.
     * @param string $b            The divisor, must not be zero.
     * @param int    $roundingMode The rounding mode.
     *
     * @throws \InvalidArgumentException  If the rounding mode is invalid.
     * @throws RoundingNecessaryException If RoundingMode::UNNECESSARY is provided but rounding is necessary.
     *
     * @psalm-suppress ImpureFunctionCall
     */
    final public function divRound(string $a, string $b, int $roundingMode) : string
    {
        [$quotient, $remainder] = $this->divQR($a, $b);

        $hasDiscardedFraction = ($remainder !== '0');
        $isPositiveOrZero = ($a[0] === '-') === ($b[0] === '-');

        $discardedFractionSign = function() use ($remainder, $b) : int {
            $r = $this->abs($this->mul($remainder, '2'));
            $b = $this->abs($b);

            return $this->cmp($r, $b);
        };

        $increment = false;

        switch ($roundingMode) {
            case RoundingMode::UNNECESSARY:
                if ($hasDiscardedFraction) {
                    throw RoundingNecessaryException::roundingNecessary();
                }
                break;

            case RoundingMode::UP:
                $increment = $hasDiscardedFraction;
                break;

            case RoundingMode::DOWN:
                break;

            case RoundingMode::CEILING:
                $increment = $hasDiscardedFraction && $isPositiveOrZero;
                break;

            case RoundingMode::FLOOR:
                $increment = $hasDiscardedFraction && ! $isPositiveOrZero;
                break;

            case RoundingMode::HALF_UP:
                $increment = $discardedFractionSign() >= 0;
                break;

            case RoundingMode::HALF_DOWN:
                $increment = $discardedFractionSign() > 0;
                break;

            case RoundingMode::HALF_CEILING:
                $increment = $isPositiveOrZero ? $discardedFractionSign() >= 0 : $discardedFractionSign() > 0;
                break;

            case RoundingMode::HALF_FLOOR:
                $increment = $isPositiveOrZero ? $discardedFractionSign() > 0 : $discardedFractionSign() >= 0;
                break;

            case RoundingMode::HALF_EVEN:
                $lastDigit = (int) $quotient[-1];
                $lastDigitIsEven = ($lastDigit % 2 === 0);
                $increment = $lastDigitIsEven ? $discardedFractionSign() > 0 : $discardedFractionSign() >= 0;
                break;

            default:
                throw new \InvalidArgumentException('Invalid rounding mode.');
        }

        if ($increment) {
            return $this->add($quotient, $isPositiveOrZero ? '1' : '-1');
        }

        return $quotient;
    }

    /**
     * Calculates bitwise AND of two numbers.
	 * 计算两个数字的与运算
     *
     * This method can be overridden by the concrete implementation if the underlying library
     * has built-in support for bitwise operations.
     */
    public function and(string $a, string $b) : string
    {
        return $this->bitwise('and', $a, $b);
    }

    /**
     * Calculates bitwise OR of two numbers.
	 * 计算两个数字的按位或
     *
     * This method can be overridden by the concrete implementation if the underlying library
     * has built-in support for bitwise operations.
     */
    public function or(string $a, string $b) : string
    {
        return $this->bitwise('or', $a, $b);
    }

    /**
     * Calculates bitwise XOR of two numbers.
	 * 计算两个数字的按位异或
     *
     * This method can be overridden by the concrete implementation if the underlying library
     * has built-in support for bitwise operations.
     */
    public function xor(string $a, string $b) : string
    {
        return $this->bitwise('xor', $a, $b);
    }

    /**
     * Performs a bitwise operation on a decimal number.
	 * 对十进制数执行按位运算
     *
     * @param 'and'|'or'|'xor' $operator The operator to use.
     * @param string           $a        The left operand.
     * @param string           $b        The right operand.
     */
    private function bitwise(string $operator, string $a, string $b) : string
    {
        [$aNeg, $bNeg, $aDig, $bDig] = $this->init($a, $b);

        $aBin = $this->toBinary($aDig);
        $bBin = $this->toBinary($bDig);

        $aLen = \strlen($aBin);
        $bLen = \strlen($bBin);

        if ($aLen > $bLen) {
            $bBin = \str_repeat("\x00", $aLen - $bLen) . $bBin;
        } elseif ($bLen > $aLen) {
            $aBin = \str_repeat("\x00", $bLen - $aLen) . $aBin;
        }

        if ($aNeg) {
            $aBin = $this->twosComplement($aBin);
        }
        if ($bNeg) {
            $bBin = $this->twosComplement($bBin);
        }

        switch ($operator) {
            case 'and':
                $value = $aBin & $bBin;
                $negative = ($aNeg and $bNeg);
                break;

            case 'or':
                $value = $aBin | $bBin;
                $negative = ($aNeg or $bNeg);
                break;

            case 'xor':
                $value = $aBin ^ $bBin;
                $negative = ($aNeg xor $bNeg);
                break;

            // @codeCoverageIgnoreStart
            default:
                throw new \InvalidArgumentException('Invalid bitwise operator.');
            // @codeCoverageIgnoreEnd
        }

        if ($negative) {
            $value = $this->twosComplement($value);
        }

        $result = $this->toDecimal($value);

        return $negative ? $this->neg($result) : $result;
    }

    /**
     * @param string $number A positive, binary number.
     */
    private function twosComplement(string $number) : string
    {
        $xor = \str_repeat("\xff", \strlen($number));

        $number ^= $xor;

        for ($i = \strlen($number) - 1; $i >= 0; $i--) {
            $byte = \ord($number[$i]);

            if (++$byte !== 256) {
                $number[$i] = \chr($byte);
                break;
            }

            $number[$i] = "\x00";

            if ($i === 0) {
                $number = "\x01" . $number;
            }
        }

        return $number;
    }

    /**
     * Converts a decimal number to a binary string.
	 * 将十进制数转换为二进制字符串
     *
     * @param string $number The number to convert, positive or zero, only digits.
     */
    private function toBinary(string $number) : string
    {
        $result = '';

        while ($number !== '0') {
            [$number, $remainder] = $this->divQR($number, '256');
            $result .= \chr((int) $remainder);
        }

        return \strrev($result);
    }

    /**
     * Returns the positive decimal representation of a binary number.
	 * 返回二进制数的正十进制表示形式
     *
     * @param string $bytes The bytes representing the number.
     */
    private function toDecimal(string $bytes) : string
    {
        $result = '0';
        $power = '1';

        for ($i = \strlen($bytes) - 1; $i >= 0; $i--) {
            $index = \ord($bytes[$i]);

            if ($index !== 0) {
                $result = $this->add($result, ($index === 1)
                    ? $power
                    : $this->mul($power, (string) $index)
                );
            }

            if ($i !== 0) {
                $power = $this->mul($power, '256');
            }
        }

        return $result;
    }
}
