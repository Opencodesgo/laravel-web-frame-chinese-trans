<?php declare(strict_types=1);

/**
 * Monolog，Handler，可格式化处理程序接口
 */

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Handler;

use Monolog\Formatter\FormatterInterface;

/**
 * Interface to describe loggers that have a formatter
 * 接口，用于描述具有格式化程序的日志记录器。
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
interface FormattableHandlerInterface
{
    /**
     * Sets the formatter.
	 * 设置格式化程序
     *
     * @param  FormatterInterface $formatter
     * @return HandlerInterface   self
     */
    public function setFormatter(FormatterInterface $formatter): HandlerInterface;

    /**
     * Gets the formatter.
	 * 得到格式化程序
     *
     * @return FormatterInterface
     */
    public function getFormatter(): FormatterInterface;
}
