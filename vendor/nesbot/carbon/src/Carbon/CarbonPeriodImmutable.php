<?php
/**
 * Carbon，Carbon 周期不变的
 */

/**
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Carbon;

class CarbonPeriodImmutable extends CarbonPeriod
{
    /**
     * Default date class of iteration items.
	 * 迭代项的默认日期类
     *
     * @var string
     */
    protected const DEFAULT_DATE_CLASS = CarbonImmutable::class;

    /**
     * Date class of iteration items.
	 * 迭代项的日期类
     *
     * @var string
     */
    protected $dateClass = CarbonImmutable::class;

    /**
     * Prepare the instance to be set (self if mutable to be mutated,
     * copy if immutable to generate a new instance).
	 * 准备要设置的实例（self，如果是可变的，则是可变的，复制不可变的以生成一个新实例）
     *
     * @return static
     */
    protected function copyIfImmutable()
    {
        return $this->constructed ? clone $this : $this;
    }
}
