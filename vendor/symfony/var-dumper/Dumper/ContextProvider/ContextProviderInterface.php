<?php
/**
 * Symfony，Component，VarDumper，转储，上下文提供者，上下文提供者接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\VarDumper\Dumper\ContextProvider;

/**
 * Interface to provide contextual data about dump data clones sent to a server.
 * 接口提供关于转储数据克隆发送到服务器的上下文数据
 *
 * @author Maxime Steinhausser <maxime.steinhausser@gmail.com>
 */
interface ContextProviderInterface
{
    public function getContext(): ?array;
}
