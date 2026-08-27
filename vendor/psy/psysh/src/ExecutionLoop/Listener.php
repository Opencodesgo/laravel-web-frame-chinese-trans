<?php
/**
 * Psy，循环执行，监听器
 */

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2023 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\ExecutionLoop;

use Psy\Shell;

/**
 * Execution Loop Listener interface.
 * 执行循环监听器接口。
 */
interface Listener
{
    /**
     * Determines whether this listener should be active.
	 * 确定此侦听器是否应处于活动状态
     */
    public static function isSupported(): bool;

    /**
     * Called once before the REPL session starts.
	 * 在REPL会话开始之前调用一次
     *
     * @param Shell $shell
     */
    public function beforeRun(Shell $shell);

    /**
     * Called at the start of each loop.
	 * 在每个循环开始时调用
     *
     * @param Shell $shell
     */
    public function beforeLoop(Shell $shell);

    /**
     * Called on user input.
	 * 在用户输入时调用。
     *
     * Return a new string to override or rewrite user input.
     *
     * @param Shell  $shell
     * @param string $input
     *
     * @return string|null User input override
     */
    public function onInput(Shell $shell, string $input);

    /**
     * Called before executing user code.
	 * 在执行用户代码之前调用。
     *
     * Return a new string to override or rewrite user code.
     *
     * Note that this is run *after* the Code Cleaner, so if you return invalid
     * or unsafe PHP here, it'll be executed without any of the safety Code
     * Cleaner provides. This comes with the big kid warranty :)
     *
     * @param Shell  $shell
     * @param string $code
     *
     * @return string|null User code override
     */
    public function onExecute(Shell $shell, string $code);

    /**
     * Called at the end of each loop.
	 * 在每个循环结束时调用
     *
     * @param Shell $shell
     */
    public function afterLoop(Shell $shell);

    /**
     * Called once after the REPL session ends.
	 * 在REPL会话结束后调用一次
     *
     * @param Shell $shell
     */
    public function afterRun(Shell $shell);
}
