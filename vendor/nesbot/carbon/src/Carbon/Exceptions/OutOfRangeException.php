<?php
/**
 * Carbon，异常，超出范围异常 
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

use InvalidArgumentException as BaseInvalidArgumentException;
use Throwable;

// This will extends OutOfRangeException instead of InvalidArgumentException since 3.0.0
// use OutOfRangeException as BaseOutOfRangeException;
// 这将从3.0.0扩展OutOfRangeException而不是InvalidArgumentException

class OutOfRangeException extends BaseInvalidArgumentException implements InvalidArgumentException
{
    /**
     * The unit or name of the value.
	 * 值的单位或名称
     *
     * @var string
     */
    private $unit;

    /**
     * The range minimum.
	 * 最小值范围
     *
     * @var mixed
     */
    private $min;

    /**
     * The range maximum.
	 * 最大值范围
     *
     * @var mixed
     */
    private $max;

    /**
     * The invalid value.
	 * 无效值
     *
     * @var mixed
     */
    private $value;

    /**
     * Constructor.
	 * 构造方法
     *
     * @param string         $unit
     * @param mixed          $min
     * @param mixed          $max
     * @param mixed          $value
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($unit, $min, $max, $value, $code = 0, ?Throwable $previous = null)
    {
        $this->unit = $unit;
        $this->min = $min;
        $this->max = $max;
        $this->value = $value;

        parent::__construct("$unit must be between $min and $max, $value given", $code, $previous);
    }

    /**
     * @return mixed
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @return mixed
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @return mixed
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
