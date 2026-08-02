<?php declare(strict_types=1);

/**
 * Monolog，处理程序，可处理处理程序接口
 *

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Handler;

use Monolog\Processor\ProcessorInterface;

/**
 * Interface to describe loggers that have processors
 * 接口来描述拥有处理器的日志记录器
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 *
 * @phpstan-import-type Record from \Monolog\Logger
 */
interface ProcessableHandlerInterface
{
    /**
     * Adds a processor in the stack.
	 * 在堆栈中添加一个处理器
     *
     * @psalm-param ProcessorInterface|callable(Record): Record $callback
     *
     * @param  ProcessorInterface|callable $callback
     * @return HandlerInterface            self
     */
    public function pushProcessor(callable $callback): HandlerInterface;

    /**
     * Removes the processor on top of the stack and returns it.
	 * 在堆栈顶部删除处理器并返回它
     *
     * @psalm-return ProcessorInterface|callable(Record): Record $callback
     *
     * @throws \LogicException             In case the processor stack is empty
     * @return callable|ProcessorInterface
     */
    public function popProcessor(): callable;
}
