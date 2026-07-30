<?php
/**
 * Carbon，异常，单位未配置异常
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

class UnitNotConfiguredException extends UnitException
{
    /**
     * The unit.
	 * 单位
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

        parent::__construct("Unit $unit have no configuration to get total from other units.", $code, $previous);
    }

    /**
     * Get the unit.
	 * 得到单位
     *
     * @return string
     */
    public function getUnit(): string
    {
        return $this->unit;
    }
}
