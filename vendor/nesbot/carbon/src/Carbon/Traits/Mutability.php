<?php
/**
 * Carbon，特性，易变性
 */

/**
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Carbon\Traits;

use Carbon\Carbon;
use Carbon\CarbonImmutable;

/**
 * Trait Mutability.
 * 可变性特征。
 *
 * Utils to know if the current object is mutable or immutable and convert it.
 * Utils用于了解当前对象是可变的还是不可变的，并对其进行转换。
 */
trait Mutability
{
    use Cast;

    /**
     * Returns true if the current class/instance is mutable.
	 * 如果当前类/实例是可变的，则返回true。
     *
     * @return bool
     */
    public static function isMutable()
    {
        return false;
    }

    /**
     * Returns true if the current class/instance is immutable.
	 * 如果当前类/实例是不可变的，则返回true。
     *
     * @return bool
     */
    public static function isImmutable()
    {
        return !static::isMutable();
    }

    /**
     * Return a mutable copy of the instance.
     *
     * @return Carbon
     */
    public function toMutable()
    {
        /** @var Carbon $date */
        $date = $this->cast(Carbon::class);

        return $date;
    }

    /**
     * Return a immutable copy of the instance.
     *
     * @return CarbonImmutable
     */
    public function toImmutable()
    {
        /** @var CarbonImmutable $date */
        $date = $this->cast(CarbonImmutable::class);

        return $date;
    }
}
