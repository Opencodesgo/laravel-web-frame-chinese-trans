<?php
/**
 * Illuminate，总线，调度程序
 */

namespace Illuminate\Bus;

use Closure;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingChain;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Jobs\SyncJob;
use Illuminate\Support\Collection;
use RuntimeException;

class Dispatcher implements QueueingDispatcher
{
    /**
     * The container implementation.
	 * 容器实现
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * The pipeline instance for the bus.
	 * 总线的管道实例
     *
     * @var \Illuminate\Pipeline\Pipeline
     */
    protected $pipeline;

    /**
     * The pipes to send commands through before dispatching.
	 * 在调度之前发送命令的管道
     *
     * @var array
     */
    protected $pipes = [];

    /**
     * The command to handler mapping for non-self-handling events.
	 * 非自处理事件到处理程序映射的命令
     *
     * @var array
     */
    protected $handlers = [];

    /**
     * The queue resolver callback.
	 * 队列解析器回调
     *
     * @var \Closure|null
     */
    protected $queueResolver;

    /**
     * Create a new command dispatcher instance.
	 * 创建一个新的命令调度程序实例
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @param  \Closure|null  $queueResolver
     * @return void
     */
    public function __construct(Container $container, Closure $queueResolver = null)
    {
        $this->container = $container;
        $this->queueResolver = $queueResolver;
        $this->pipeline = new Pipeline($container);
    }

    /**
     * Dispatch a command to its appropriate handler.
	 * 将命令分派给相应的处理程序
     *
     * @param  mixed  $command
     * @return mixed
     */
    public function dispatch($command)
    {
        return $this->queueResolver && $this->commandShouldBeQueued($command)
                        ? $this->dispatchToQueue($command)
                        : $this->dispatchNow($command);
    }

    /**
     * Dispatch a command to its appropriate handler in the current process.
	 * 将命令分派给当前进程中相应的处理程序
     *
     * Queueable jobs will be dispatched to the "sync" queue.
     *
     * @param  mixed  $command
     * @param  mixed  $handler
     * @return mixed
     */
    public function dispatchSync($command, $handler = null)
    {
        if ($this->queueResolver &&
            $this->commandShouldBeQueued($command) &&
            method_exists($command, 'onConnection')) {
            return $this->dispatchToQueue($command->onConnection('sync'));
        }

        return $this->dispatchNow($command, $handler);
    }

    /**
     * Dispatch a command to its appropriate handler in the current process without using the synchronous queue.
	 * 在不使用同步队列的情况下，将命令分派给当前进程中相应的处理程序。
     *
     * @param  mixed  $command
     * @param  mixed  $handler
     * @return mixed
     */
    public function dispatchNow($command, $handler = null)
    {
        $uses = class_uses_recursive($command);

        if (in_array(InteractsWithQueue::class, $uses) &&
            in_array(Queueable::class, $uses) &&
            ! $command->job) {
            $command->setJob(new SyncJob($this->container, json_encode([]), 'sync', 'sync'));
        }

        if ($handler || $handler = $this->getCommandHandler($command)) {
            $callback = function ($command) use ($handler) {
                $method = method_exists($handler, 'handle') ? 'handle' : '__invoke';

                return $handler->{$method}($command);
            };
        } else {
            $callback = function ($command) {
                $method = method_exists($command, 'handle') ? 'handle' : '__invoke';

                return $this->container->call([$command, $method]);
            };
        }

        return $this->pipeline->send($command)->through($this->pipes)->then($callback);
    }

    /**
     * Attempt to find the batch with the given ID.
	 * 尝试查找具有给定ID的批处理
     *
     * @param  string  $batchId
     * @return \Illuminate\Bus\Batch|null
     */
    public function findBatch(string $batchId)
    {
        return $this->container->make(BatchRepository::class)->find($batchId);
    }

    /**
     * Create a new batch of queueable jobs.
	 * 创建一批新的可排队作业
     *
     * @param  \Illuminate\Support\Collection|array|mixed  $jobs
     * @return \Illuminate\Bus\PendingBatch
     */
    public function batch($jobs)
    {
        return new PendingBatch($this->container, Collection::wrap($jobs));
    }

    /**
     * Create a new chain of queueable jobs.
	 * 创建一个新的可排队作业链
     *
     * @param  \Illuminate\Support\Collection|array  $jobs
     * @return \Illuminate\Foundation\Bus\PendingChain
     */
    public function chain($jobs)
    {
        $jobs = Collection::wrap($jobs);

        return new PendingChain($jobs->shift(), $jobs->toArray());
    }

    /**
     * Determine if the given command has a handler.
	 * 确定给定命令是否有处理程序
     *
     * @param  mixed  $command
     * @return bool
     */
    public function hasCommandHandler($command)
    {
        return array_key_exists(get_class($command), $this->handlers);
    }

    /**
     * Retrieve the handler for a command.
	 * 检索命令的处理程序
     *
     * @param  mixed  $command
     * @return bool|mixed
     */
    public function getCommandHandler($command)
    {
        if ($this->hasCommandHandler($command)) {
            return $this->container->make($this->handlers[get_class($command)]);
        }

        return false;
    }

    /**
     * Determine if the given command should be queued.
	 * 确定是否应该对给定的命令进行排队
     *
     * @param  mixed  $command
     * @return bool
     */
    protected function commandShouldBeQueued($command)
    {
        return $command instanceof ShouldQueue;
    }

    /**
     * Dispatch a command to its appropriate handler behind a queue.
	 * 将命令分派到队列后面相应的处理程序
     *
     * @param  mixed  $command
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public function dispatchToQueue($command)
    {
        $connection = $command->connection ?? null;

        $queue = call_user_func($this->queueResolver, $connection);

        if (! $queue instanceof Queue) {
            throw new RuntimeException('Queue resolver did not return a Queue implementation.');
        }

        if (method_exists($command, 'queue')) {
            return $command->queue($queue, $command);
        }

        return $this->pushCommandToQueue($queue, $command);
    }

    /**
     * Push the command onto the given queue instance.
	 * 将命令推入给定的队列实例
     *
     * @param  \Illuminate\Contracts\Queue\Queue  $queue
     * @param  mixed  $command
     * @return mixed
     */
    protected function pushCommandToQueue($queue, $command)
    {
        if (isset($command->queue, $command->delay)) {
            return $queue->laterOn($command->queue, $command->delay, $command);
        }

        if (isset($command->queue)) {
            return $queue->pushOn($command->queue, $command);
        }

        if (isset($command->delay)) {
            return $queue->later($command->delay, $command);
        }

        return $queue->push($command);
    }

    /**
     * Dispatch a command to its appropriate handler after the current process.
	 * 在当前进程结束后，将命令分派给相应的处理程序。
     *
     * @param  mixed  $command
     * @param  mixed  $handler
     * @return void
     */
    public function dispatchAfterResponse($command, $handler = null)
    {
        $this->container->terminating(function () use ($command, $handler) {
            $this->dispatchSync($command, $handler);
        });
    }

    /**
     * Set the pipes through which commands should be piped before dispatching.
	 * 在调度之前，设置命令应该通过的管道。
     *
     * @param  array  $pipes
     * @return $this
     */
    public function pipeThrough(array $pipes)
    {
        $this->pipes = $pipes;

        return $this;
    }

    /**
     * Map a command to a handler.
	 * 将命令映射到处理程序
     *
     * @param  array  $map
     * @return $this
     */
    public function map(array $map)
    {
        $this->handlers = array_merge($this->handlers, $map);

        return $this;
    }
}
