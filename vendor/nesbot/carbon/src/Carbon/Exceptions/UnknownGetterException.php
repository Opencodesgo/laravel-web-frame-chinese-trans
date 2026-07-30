<?php
/**
 * Carbon，异常，未知Getter异常
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

class UnknownGetterException extends BaseInvalidArgumentException implements InvalidArgumentException
{
    /**
     * The getter.
	 * getter
     *
     * @var string
     */
    protected $getter;

    /**
     * Constructor.
	 * 构造函数
     *
     * @param string         $getter   getter name
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($getter, $code = 0, ?Throwable $previous = null)
    {
        $this->getter = $getter;

        parent::__construct("Unknown getter '$getter'", $code, $previous);
    }

    /**
     * Get the getter.
     *
     * @return string
     */
    public function getGetter(): string
    {
        return $this->getter;
    }
}
