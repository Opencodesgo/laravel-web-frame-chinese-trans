<?php declare(strict_types=1);

/**
 * Monolog，Handler，指针交叉，激活策略接口
 */

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Handler\FingersCrossed;

/**
 * Interface for activation strategies for the FingersCrossedHandler.
 * 激活FingersCrossedHandler策略接口
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * @phpstan-import-type Record from \Monolog\Logger
 */
interface ActivationStrategyInterface
{
    /**
     * Returns whether the given record activates the handler.
	 * 返回给定记录是否激活处理程序
     *
     * @phpstan-param Record $record
     */
    public function isHandlerActivated(array $record): bool;
}
