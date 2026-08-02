<?php
/**
 * Symfony，Component，Console，命令加载，工厂命令加载器
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

use Symfony\Component\Console\Exception\CommandNotFoundException;

/**
 * A simple command loader using factories to instantiate commands lazily.
 * 一个简单的命令加载器，使用工厂惰性地实例化命令。
 *
 * @author Maxime Steinhausser <maxime.steinhausser@gmail.com>
 */
class FactoryCommandLoader implements CommandLoaderInterface
{
    private $factories;

    /**
     * @param callable[] $factories Indexed by command names
     */
    public function __construct(array $factories)
    {
        $this->factories = $factories;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name)
    {
        return isset($this->factories[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name)
    {
        if (!isset($this->factories[$name])) {
            throw new CommandNotFoundException(sprintf('Command "%s" does not exist.', $name));
        }

        $factory = $this->factories[$name];

        return $factory();
    }

    /**
     * {@inheritdoc}
     */
    public function getNames()
    {
        return array_keys($this->factories);
    }
}
