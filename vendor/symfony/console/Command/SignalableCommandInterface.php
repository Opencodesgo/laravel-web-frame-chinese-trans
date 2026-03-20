<?php
/**
 * Symfony，Component，Console，命令，Signalable 命令接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Command;

/**
 * Interface for command reacting to signal.
 * 命令对信号作出反应的接口
 *
 * @author Grégoire Pineau <lyrixx@lyrix.info>
 */
interface SignalableCommandInterface
{
    /**
     * Returns the list of signals to subscribe.
	 * 返回要订阅的信号列表
     */
    public function getSubscribedSignals(): array;

    /**
     * The method will be called when the application is signaled.
	 * 该方法将在应用程序收到信号时调用
     */
    public function handleSignal(int $signal): void;
}
