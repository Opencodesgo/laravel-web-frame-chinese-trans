<?php
/**
 * Carbon，异常，不是Carbon类异常
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

use Carbon\CarbonInterface;
use InvalidArgumentException as BaseInvalidArgumentException;
use Throwable;

class NotACarbonClassException extends BaseInvalidArgumentException implements InvalidArgumentException
{
    /**
     * The className.
	 * 类名
     *
     * @var string
     */
    protected $className;

    /**
     * Constructor.
	 * 构造函数
     *
     * @param string         $className
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($className, $code = 0, ?Throwable $previous = null)
    {
        $this->className = $className;

        parent::__construct(\sprintf('Given class does not implement %s: %s', CarbonInterface::class, $className), $code, $previous);
    }

    /**
     * Get the className.
	 * 获取className
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }
}
