<?php
/**
 * Symfony，Component，Console，命令，命令加载器，命令加载器接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\CommandLoader;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\CommandNotFoundException;

/**
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
interface CommandLoaderInterface
{
    /**
     * Loads a command.
	 * 加载命令
     *
     * @throws CommandNotFoundException
     */
    public function get(string $name): Command;

    /**
     * Checks if a command exists.
	 * 检查命令是否存在
     */
    public function has(string $name): bool;

    /**
     * @return string[]
     */
    public function getNames(): array;
}
