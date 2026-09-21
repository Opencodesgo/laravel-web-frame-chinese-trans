<?php
/**
 * Illuminate，控制台，容器命令加载器
 */

namespace Illuminate\Console;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class ContainerCommandLoader implements CommandLoaderInterface
{
    /**
     * The container instance.
	 * 容器实例
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * A map of command names to classes.
	 * 命令名称到类的映射
     *
     * @var array
     */
    protected $commandMap;

    /**
     * Create a new command loader instance.
	 * 创建新的命令加载器实例
     *
     * @param  \Psr\Container\ContainerInterface  $container
     * @param  array  $commandMap
     * @return void
     */
    public function __construct(ContainerInterface $container, array $commandMap)
    {
        $this->container = $container;
        $this->commandMap = $commandMap;
    }

    /**
     * Resolve a command from the container.
	 * 解析来自容器的命令
     *
     * @param  string  $name
     * @return \Symfony\Component\Console\Command\Command
     *
     * @throws \Symfony\Component\Console\Exception\CommandNotFoundException
     */
    public function get(string $name): Command
    {
        if (! $this->has($name)) {
            throw new CommandNotFoundException(sprintf('Command "%s" does not exist.', $name));
        }

        return $this->container->get($this->commandMap[$name]);
    }

    /**
     * Determines if a command exists.
	 * 确定命令是否存在
     *
     * @param  string  $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return $name && isset($this->commandMap[$name]);
    }

    /**
     * Get the command names.
	 * 得到命令名称
     *
     * @return string[]
     */
    public function getNames(): array
    {
        return array_keys($this->commandMap);
    }
}
