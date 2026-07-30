<?php
/**
 * Carbon，异常，错误比较单元异常
 */

/**
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Carbon\Exceptions;

use Throwable;

class BadComparisonUnitException extends UnitException
{
    /**
     * The unit.
	 * 单元
     *
     * @var string
     */
    protected $unit;

    /**
     * Constructor.
	 * 构造函数
     *
     * @param string         $unit
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($unit, $code = 0, ?Throwable $previous = null)
    {
        $this->unit = $unit;

        parent::__construct("Bad comparison unit: '$unit'", $code, $previous);
    }

    /**
     * Get the unit.
	 * 得到单元
     *
     * @return string
     */
    public function getUnit(): string
    {
        return $this->unit;
    }
}
