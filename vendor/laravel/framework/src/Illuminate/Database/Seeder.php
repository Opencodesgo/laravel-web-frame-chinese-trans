<?php
/**
 * Illuminate，数据库，播种机抽象类
 */

namespace Illuminate\Database;

use Illuminate\Console\Command;
use Illuminate\Console\View\Components\TwoColumnDetail;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Arr;
use InvalidArgumentException;

abstract class Seeder
{
    /**
     * The container instance.
	 * 容器实例
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * The console command instance.
	 * 控制台命令实例
     *
     * @var \Illuminate\Console\Command
     */
    protected $command;

    /**
     * Seeders that have been called at least one time.
	 * 至少被召唤过一次的种子
     *
     * @var array
     */
    protected static $called = [];

    /**
     * Run the given seeder class.
	 * 运行给定的播种器类
     *
     * @param  array|string  $class
     * @param  bool  $silent
     * @param  array  $parameters
     * @return $this
     */
    public function call($class, $silent = false, array $parameters = [])
    {
        $classes = Arr::wrap($class);

        foreach ($classes as $class) {
            $seeder = $this->resolve($class);

            $name = get_class($seeder);

            if ($silent === false && isset($this->command)) {
                with(new TwoColumnDetail($this->command->getOutput()))->render(
                    $name,
                    '<fg=yellow;options=bold>RUNNING</>'
                );
            }

            $startTime = microtime(true);

            $seeder->__invoke($parameters);

            if ($silent === false && isset($this->command)) {
                $runTime = number_format((microtime(true) - $startTime) * 1000, 2);

                with(new TwoColumnDetail($this->command->getOutput()))->render(
                    $name,
                    "<fg=gray>$runTime ms</> <fg=green;options=bold>DONE</>"
                );

                $this->command->getOutput()->writeln('');
            }

            static::$called[] = $class;
        }

        return $this;
    }

    /**
     * Run the given seeder class.
	 * 运行给定的播种器类
     *
     * @param  array|string  $class
     * @param  array  $parameters
     * @return void
     */
    public function callWith($class, array $parameters = [])
    {
        $this->call($class, false, $parameters);
    }

    /**
     * Silently run the given seeder class.
	 * 静默地运行给定的播种器类
     *
     * @param  array|string  $class
     * @param  array  $parameters
     * @return void
     */
    public function callSilent($class, array $parameters = [])
    {
        $this->call($class, true, $parameters);
    }

    /**
     * Run the given seeder class once.
	 * 运行给定的播种器类一次
     *
     * @param  array|string  $class
     * @param  bool  $silent
     * @return void
     */
    public function callOnce($class, $silent = false, array $parameters = [])
    {
        if (in_array($class, static::$called)) {
            return;
        }

        $this->call($class, $silent, $parameters);
    }

    /**
     * Resolve an instance of the given seeder class.
	 * 解析给定种子类的实例
     *
     * @param  string  $class
     * @return \Illuminate\Database\Seeder
     */
    protected function resolve($class)
    {
        if (isset($this->container)) {
            $instance = $this->container->make($class);

            $instance->setContainer($this->container);
        } else {
            $instance = new $class;
        }

        if (isset($this->command)) {
            $instance->setCommand($this->command);
        }

        return $instance;
    }

    /**
     * Set the IoC container instance.
	 * 设置IoC容器实例
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Set the console command instance.
	 * 设置控制台命令实例
     *
     * @param  \Illuminate\Console\Command  $command
     * @return $this
     */
    public function setCommand(Command $command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Run the database seeds.
	 * 运行数据库种子
     *
     * @param  array  $parameters
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function __invoke(array $parameters = [])
    {
        if (! method_exists($this, 'run')) {
            throw new InvalidArgumentException('Method [run] missing from '.get_class($this));
        }

        $callback = fn () => isset($this->container)
            ? $this->container->call([$this, 'run'], $parameters)
            : $this->run(...$parameters);

        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[WithoutModelEvents::class])) {
            $callback = $this->withoutModelEvents($callback);
        }

        return $callback();
    }
}
