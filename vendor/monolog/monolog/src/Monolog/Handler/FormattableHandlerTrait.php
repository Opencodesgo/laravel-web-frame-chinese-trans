<?php declare(strict_types=1);

/**
 * Monolog，处理程序，可格式化的处理程序特性
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
use Monolog\Formatter\LineFormatter;

/**
 * Helper trait for implementing FormattableInterface
 * 实现FormattableInterface的辅助特性
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
trait FormattableHandlerTrait
{
    /**
     * @var ?FormatterInterface
     */
    protected $formatter;

    /**
     * {@inheritDoc}
     */
    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getFormatter(): FormatterInterface
    {
        if (!$this->formatter) {
            $this->formatter = $this->getDefaultFormatter();
        }

        return $this->formatter;
    }

    /**
     * Gets the default formatter.
	 * 获取默认格式化程序。
     *
     * Overwrite this if the LineFormatter is not a good default for your handler.
	 * 如果线性格式化程序对您的处理程序没有良好的默认值,则将此重写。
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter();
    }
}
