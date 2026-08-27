<?php
/**
 * Brick，Math，内部，计算器，Bc 数学计算器
 */

declare(strict_types=1);

namespace Brick\Math\Internal\Calculator;

use Brick\Math\Internal\Calculator;

/**
 * Calculator implementation built around the bcmath library.
 * 计算器实现围绕bcmath库构建
 *
 * @internal
 *
 * @psalm-immutable
 */
class BcMathCalculator extends Calculator
{
    public function add(string $a, string $b) : string
    {
        return \bcadd($a, $b, 0);
    }

    public function sub(string $a, string $b) : string
    {
        return \bcsub($a, $b, 0);
    }

    public function mul(string $a, string $b) : string
    {
        return \bcmul($a, $b, 0);
    }

    public function divQ(string $a, string $b) : string
    {
        return \bcdiv($a, $b, 0);
    }

    /**
     * @psalm-suppress InvalidNullableReturnType
     * @psalm-suppress NullableReturnStatement
     */
    public function divR(string $a, string $b) : string
    {
        return \bcmod($a, $b, 0);
    }

    public function divQR(string $a, string $b) : array
    {
        $q = \bcdiv($a, $b, 0);
        $r = \bcmod($a, $b, 0);

        assert($r !== null);

        return [$q, $r];
    }

    public function pow(string $a, int $e) : string
    {
        return \bcpow($a, (string) $e, 0);
    }

    public function modPow(string $base, string $exp, string $mod) : string
    {
        return \bcpowmod($base, $exp, $mod, 0);
    }

    /**
     * @psalm-suppress InvalidNullableReturnType
     * @psalm-suppress NullableReturnStatement
     */
    public function sqrt(string $n) : string
    {
        return \bcsqrt($n, 0);
    }
}
