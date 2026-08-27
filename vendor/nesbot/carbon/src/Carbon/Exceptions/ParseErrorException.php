<?php
/**
 * Carbon，异常，解析错误异常 
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

class ParseErrorException extends BaseInvalidArgumentException implements InvalidArgumentException
{
    /**
     * The expected.
	 * 期望值
     *
     * @var string
     */
    protected $expected;

    /**
     * The actual.
	 * 实际
     *
     * @var string
     */
    protected $actual;

    /**
     * The help message.
	 * 帮助消息
     *
     * @var string
     */
    protected $help;

    /**
     * Constructor.
	 * 构造方法
     *
     * @param string         $expected
     * @param string         $actual
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($expected, $actual, $help = '', $code = 0, ?Throwable $previous = null)
    {
        $this->expected = $expected;
        $this->actual = $actual;
        $this->help = $help;

        $actual = $actual === '' ? 'data is missing' : "get '$actual'";

        parent::__construct(trim("Format expected $expected but $actual\n$help"), $code, $previous);
    }

    /**
     * Get the expected.
	 * 得到预期的结果
     *
     * @return string
     */
    public function getExpected(): string
    {
        return $this->expected;
    }

    /**
     * Get the actual.
	 * 得到实际的
     *
     * @return string
     */
    public function getActual(): string
    {
        return $this->actual;
    }

    /**
     * Get the help message.
	 * 获取帮助信息
     *
     * @return string
     */
    public function getHelp(): string
    {
        return $this->help;
    }
}
