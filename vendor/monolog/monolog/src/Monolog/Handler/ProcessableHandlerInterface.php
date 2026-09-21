<?php declare(strict_types=1);

/**
 * Monolog，处理器，可加工的处理程序接口
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

use Monolog\Processor\ProcessorInterface;

/**
 * Interface to describe loggers that have processors
 * 接口来描述具有处理器的记录器
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 *
 * @phpstan-import-type Record from \Monolog\Logger
 */
interface ProcessableHandlerInterface
{
    /**
     * Adds a processor in the stack.
	 * 在堆栈中添加处理器
     *
     * @psalm-param ProcessorInterface|callable(Record): Record $callback
     *
     * @param  ProcessorInterface|callable $callback
     * @return HandlerInterface            self
     */
    public function pushProcessor(callable $callback): HandlerInterface;

    /**
     * Removes the processor on top of the stack and returns it.
	 * 移除堆栈顶部的处理器并返回它
     *
     * @psalm-return ProcessorInterface|callable(Record): Record $callback
     *
     * @throws \LogicException             In case the processor stack is empty
     * @return callable|ProcessorInterface
     */
    public function popProcessor(): callable;
}
