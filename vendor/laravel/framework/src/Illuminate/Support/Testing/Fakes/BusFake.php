<?php
/**
 * Illuminate，支持，测试，佯装，Bus 佯装
 */

namespace Illuminate\Support\Testing\Fakes;

use Closure;
use Illuminate\Bus\BatchRepository;
use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ReflectsClosures;
use PHPUnit\Framework\Assert as PHPUnit;

class BusFake implements QueueingDispatcher
{
    use ReflectsClosures;

    /**
     * The original Bus dispatcher implementation.
	 * 原始总线调度程序实现
     *
     * @var \Illuminate\Contracts\Bus\QueueingDispatcher
     */
    protected $dispatcher;

    /**
     * The job types that should be intercepted instead of dispatched.
	 * 应该拦截而不是分派的作业类型
     *
     * @var array
     */
    protected $jobsToFake = [];

    /**
     * The job types that should be dispatched instead of faked.
	 * 应该派遣而不是伪造的工作类型
     *
     * @var array
     */
    protected $jobsToDispatch = [];

    /**
     * The fake repository to track batched jobs.
	 * 用于跟踪批处理作业的假存储库
     *
     * @var \Illuminate\Bus\BatchRepository
     */
    protected $batchRepository;

    /**
     * The commands that have been dispatched.
	 * 已调度的命令
     *
     * @var array
     */
    protected $commands = [];

    /**
     * The commands that have been dispatched synchronously.
	 * 已同步调度的命令
     *
     * @var array
     */
    protected $commandsSync = [];

    /**
     * The commands that have been dispatched after the response has been sent.
	 * 在响应发送后已分派的命令
     *
     * @var array
     */
    protected $commandsAfterResponse = [];

    /**
     * The batches that have been dispatched.
	 * 已发送的批次
     *
     * @var array
     */
    protected $batches = [];

    /**
     * Create a new bus fake instance.
	 * 创建一个新的总线伪实例
     *
     * @param  \Illuminate\Contracts\Bus\QueueingDispatcher  $dispatcher
     * @param  array|string  $jobsToFake
     * @param  \Illuminate\Bus\BatchRepository|null  $batchRepository
     * @return void
     */
    public function __construct(QueueingDispatcher $dispatcher, $jobsToFake = [], BatchRepository $batchRepository = null)
    {
        $this->dispatcher = $dispatcher;
        $this->jobsToFake = Arr::wrap($jobsToFake);
        $this->batchRepository = $batchRepository ?: new BatchRepositoryFake;
    }

    /**
     * Specify the jobs that should be dispatched instead of faked.
	 * 指定应该发送而不是伪造的作业
     *
     * @param  array|string  $jobsToDispatch
     * @return void
     */
    public function except($jobsToDispatch)
    {
        $this->jobsToDispatch = array_merge($this->jobsToDispatch, Arr::wrap($jobsToDispatch));

        return $this;
    }

    /**
     * Assert if a job was dispatched based on a truth-test callback.
	 * 断言作业是否基于真值测试回调进行分派
     *
     * @param  string|\Closure  $command
     * @param  callable|int|null  $callback
     * @return void
     */
    public function assertDispatched($command, $callback = null)
    {
        if ($command instanceof Closure) {
            [$command, $callback] = [$this->firstClosureParameterType($command), $command];
        }

        if (is_numeric($callback)) {
            return $this->assertDispatchedTimes($command, $callback);
        }

        PHPUnit::assertTrue(
            $this->dispatched($command, $callback)->count() > 0 ||
            $this->dispatchedAfterResponse($command, $callback)->count() > 0 ||
            $this->dispatchedSync($command, $callback)->count() > 0,
            "The expected [{$command}] job was not dispatched."
        );
    }

    /**
     * Assert if a job was pushed a number of times.
	 * 判断一个作业是否被推送了多次
     *
     * @param  string|\Closure  $command
     * @param  int  $times
     * @return void
     */
    public function assertDispatchedTimes($command, $times = 1)
    {
        $callback = null;

        if ($command instanceof Closure) {
            [$command, $callback] = [$this->firstClosureParameterType($command), $command];
        }

        $count = $this->dispatched($command, $callback)->count() +
                 $this->dispatchedAfterResponse($command, $callback)->count() +
                 $this->dispatchedSync($command, $callback)->count();

        PHPUnit::assertSame(
            $times, $count,
            "The expected [{$command}] job was pushed {$count} times instead of {$times} times."
        );
    }

    /**
     * Determine if a job was dispatched based on a truth-test callback.
	 * 确定是否根据true -test回调分派了作业
     *
     * @param  string|\Closure  $command
     * @param  callable|null  $callback
     * @return void
     */
    public function assertNotDispatched($command, $callback = null)
    {
        if ($command instanceof Closure) {
            [$command, $callback] = [$this->firstClosureParameterType($command), $command];
        }

        PHPUnit::assertTrue(
            $this->dispatched($command, $callback)->count() === 0 &&
            $this->dispatchedAfterResponse($command, $callback)->count() === 0 &&
            $this->dispatchedSync($command, $callback)->count() === 0,
            "The unexpected [{$command}] job was dispatched."
        );
    }

    /**
     * Assert that no jobs were dispatched.
	 * 断言没有分派作业
     *
     * @return void
     */
    public function assertNothingDispatched()
    {
        PHPUnit::assertEmpty($this->commands, 'Jobs were dispatched unexpectedly.');
    }

    /**
     * Assert if a job was explicitly dispatched synchronously based on a truth-test callback.
	 * 断言作业是否基于真值测试回调显式地同步调度
     *
     * @param  string|\Closure  $command
     * @param  callable|int|null  $callback
     * @return void
     */
    public function assertDispatchedSync($command, $callback = null)
    {
        if ($command instanceof Closure) {
            [$command, $callback] = [$this->firstClosureParameterType($command), $command];
        }

        if (is_numeric($callback)) {
            return $this->assertDispatchedSyncTimes($command, $callback);
        }

        PHPUnit::assertTrue(
            $this->dispatchedSync($command, $callback)->count() > 0,
            "The expected [{$command}] job was not dispatched synchronously."
        );
    }

    /**
     * Assert if a job was pushed synchronously a number of times.
	 * 判断一个作业是否被同步推送了多次
     *
     * @param  string|\Closure  $command
     * @param  int  $times
     * @return void
     */
    public function assertDispatchedSyncTimes($command, $times = 1)
    {
        $callback = null;

        if ($command instanceof Closure) {
            [$command, $callback] = [$this->firstClosureParameterType($command), $command];
        }

        $count = $this->dispatchedSync($command, $callback)->count();

        PHPUnit::assertSame(
            $times, $count,
            "The expected [{$command}] job was synchronously pushed {$count} times instead of {$times} times."
        );
    }

    /**
     * Determine if a job was dispatched based on a truth-test callback.
	 * 确定是否根据true -test回调分派了作业
     *
     * @param  string|\Closure  $command
     * @param  callable|null  $callback
     * @return void
     */
    public function assertNotDispatchedSync($command, $callback = null)
    {
        if ($command instanceof Closure) {
            [$command, $callback] = [$this->firstClosureParameterType($command), $command];
        }

        PHPUnit::assertCount(
            0, $this->dispatchedSync($command, $callback),
            "The unexpected [{$command}] job was dispatched synchronously."
        );
    }

    /**
     * Assert if a job was dispatched after the response was sent based on a truth-test callback.
	 * 根据true -test回调发送响应后，判断是否分派了作业。
     *
     * @param  string|\Closure  $command
     * @param  callable|int|null  $callback
     * @return void
     */
    public function assertDispatchedAfterResponse($command, $callback = null)
    {
        if ($command instanceof Closure) {
            [$command, $callback] = [$this->firstClosureParameterType($command), $command];
        }

        if (is_numeric($callback)) {
            return $this->assertDispatchedAfterResponseTimes($command, $callback);
        }

        PHPUnit::assertTrue(
            $this->dispatchedAfterResponse($command, $callback)->count() > 0,
            "The expected [{$command}] job was not dispatched after sending the response."
        );
    }

    /**
     * Assert if a job was pushed after the response was sent a number of times.
	 * 断言在发送响应多次后是否推送了作业
     *
     * @param  string|\Closure  $command
     * @param  int  $times
     * @return void
     */
    public function assertDispatchedAfterResponseTimes($command, $times = 1)
    {
        $callback = null;

        if ($command instanceof Closure) {
            [$command, $callback] = [$this->firstClosureParameterType($command), $command];
        }

        $count = $this->dispatchedAfterResponse($command, $callback)->count();

        PHPUnit::assertSame(
            $times, $count,
            "The expected [{$command}] job was pushed {$count} times instead of {$times} times."
        );
    }

    /**
     * Determine if a job was dispatched based on a truth-test callback.
	 * 确定是否根据true -test回调分派了作业。
     *
     * @param  string|\Closure  $command
     * @param  callable|null  $callback
     * @return void
     */
    public function assertNotDispatchedAfterResponse($command, $callback = null)
    {
        if ($command instanceof Closure) {
            [$command, $callback] = [$this->firstClosureParameterType($command), $command];
        }

        PHPUnit::assertCount(
            0, $this->dispatchedAfterResponse($command, $callback),
            "The unexpected [{$command}] job was dispatched after sending the response."
        );
    }

    /**
     * Assert if a chain of jobs was dispatched.
	 * 判断是否分派了作业链
     *
     * @param  array  $expectedChain
     * @return void
     */
    public function assertChained(array $expectedChain)
    {
        $command = $expectedChain[0];

        $expectedChain = array_slice($expectedChain, 1);

        $callback = null;

        if ($command instanceof Closure) {
            [$command, $callback] = [$this->firstClosureParameterType($command), $command];
        } elseif (! is_string($command)) {
            $instance = $command;

            $command = get_class($instance);

            $callback = function ($job) use ($instance) {
                return serialize($this->resetChainPropertiesToDefaults($job)) === serialize($instance);
            };
        }

        PHPUnit::assertTrue(
            $this->dispatched($command, $callback)->isNotEmpty(),
            "The expected [{$command}] job was not dispatched."
        );

        PHPUnit::assertTrue(
            collect($expectedChain)->isNotEmpty(),
            'The expected chain can not be empty.'
        );

        $this->isChainOfObjects($expectedChain)
            ? $this->assertDispatchedWithChainOfObjects($command, $expectedChain, $callback)
            : $this->assertDispatchedWithChainOfClasses($command, $expectedChain, $callback);
    }

    /**
     * Reset the chain properties to their default values on the job.
	 * 在作业中将链属性重置为其默认值
     *
     * @param  mixed  $job
     * @return mixed
     */
    protected function resetChainPropertiesToDefaults($job)
    {
        return tap(clone $job, function ($job) {
            $job->chainConnection = null;
            $job->chainQueue = null;
            $job->chainCatchCallbacks = null;
            $job->chained = [];
        });
    }

    /**
     * Assert if a job was dispatched with an empty chain based on a truth-test callback.
	 * 判断作业是否使用基于真值测试回调的空链进行分派
     *
     * @param  string|\Closure  $command
     * @param  callable|null  $callback
     * @return void
     */
    public function assertDispatchedWithoutChain($command, $callback = null)
    {
        if ($command instanceof Closure) {
            [$command, $callback] = [$this->firstClosureParameterType($command), $command];
        }

        PHPUnit::assertTrue(
            $this->dispatched($command, $callback)->isNotEmpty(),
            "The expected [{$command}] job was not dispatched."
        );

        $this->assertDispatchedWithChainOfClasses($command, [], $callback);
    }

    /**
     * Assert if a job was dispatched with chained jobs based on a truth-test callback.
	 * 判断一个作业是否基于true -test回调被链式作业分配
     *
     * @param  string  $command
     * @param  array  $expectedChain
     * @param  callable|null  $callback
     * @return void
     */
    protected function assertDispatchedWithChainOfObjects($command, $expectedChain, $callback)
    {
        $chain = collect($expectedChain)->map(fn ($job) => serialize($job))->all();

        PHPUnit::assertTrue(
            $this->dispatched($command, $callback)->filter(
                fn ($job) => $job->chained == $chain
            )->isNotEmpty(),
            'The expected chain was not dispatched.'
        );
    }

    /**
     * Assert if a job was dispatched with chained jobs based on a truth-test callback.
	 * 判断一个作业是否基于true -test回调被链式作业分配
     *
     * @param  string  $command
     * @param  array  $expectedChain
     * @param  callable|null  $callback
     * @return void
     */
    protected function assertDispatchedWithChainOfClasses($command, $expectedChain, $callback)
    {
        $matching = $this->dispatched($command, $callback)->map->chained->map(function ($chain) {
            return collect($chain)->map(
                fn ($job) => get_class(unserialize($job))
            );
        })->filter(
            fn ($chain) => $chain->all() === $expectedChain
        );

        PHPUnit::assertTrue(
            $matching->isNotEmpty(), 'The expected chain was not dispatched.'
        );
    }

    /**
     * Determine if the given chain is entirely composed of objects.
	 * 确定给定链是否完全由对象组成
     *
     * @param  array  $chain
     * @return bool
     */
    protected function isChainOfObjects($chain)
    {
        return ! collect($chain)->contains(fn ($job) => ! is_object($job));
    }

    /**
     * Assert if a batch was dispatched based on a truth-test callback.
	 * 判断批处理是否基于真值测试回调进行分派
     *
     * @param  callable  $callback
     * @return void
     */
    public function assertBatched(callable $callback)
    {
        PHPUnit::assertTrue(
            $this->batched($callback)->count() > 0,
            'The expected batch was not dispatched.'
        );
    }

    /**
     * Assert the number of batches that have been dispatched.
	 * 断言已分派的批的数量
     *
     * @param  int  $count
     * @return void
     */
    public function assertBatchCount($count)
    {
        PHPUnit::assertCount(
            $count, $this->batches,
        );
    }

    /**
     * Assert that no batched jobs were dispatched.
	 * 断言没有分派批处理作业
     *
     * @return void
     */
    public function assertNothingBatched()
    {
        PHPUnit::assertEmpty($this->batches, 'Batched jobs were dispatched unexpectedly.');
    }

    /**
     * Get all of the jobs matching a truth-test callback.
	 * 获取所有符合真实测试回调的工作
     *
     * @param  string  $command
     * @param  callable|null  $callback
     * @return \Illuminate\Support\Collection
     */
    public function dispatched($command, $callback = null)
    {
        if (! $this->hasDispatched($command)) {
            return collect();
        }

        $callback = $callback ?: fn () => true;

        return collect($this->commands[$command])->filter(fn ($command) => $callback($command));
    }

    /**
     * Get all of the jobs dispatched synchronously matching a truth-test callback.
	 * 获取与true -test回调相匹配的同步调度的所有作业
     *
     * @param  string  $command
     * @param  callable|null  $callback
     * @return \Illuminate\Support\Collection
     */
    public function dispatchedSync(string $command, $callback = null)
    {
        if (! $this->hasDispatchedSync($command)) {
            return collect();
        }

        $callback = $callback ?: fn () => true;

        return collect($this->commandsSync[$command])->filter(fn ($command) => $callback($command));
    }

    /**
     * Get all of the jobs dispatched after the response was sent matching a truth-test callback.
	 * 获取响应发送后与true -test回调相匹配的所有作业
     *
     * @param  string  $command
     * @param  callable|null  $callback
     * @return \Illuminate\Support\Collection
     */
    public function dispatchedAfterResponse(string $command, $callback = null)
    {
        if (! $this->hasDispatchedAfterResponse($command)) {
            return collect();
        }

        $callback = $callback ?: fn () => true;

        return collect($this->commandsAfterResponse[$command])->filter(fn ($command) => $callback($command));
    }

    /**
     * Get all of the pending batches matching a truth-test callback.
	 * 获取与true -test回调匹配的所有待处理批次
     *
     * @param  callable  $callback
     * @return \Illuminate\Support\Collection
     */
    public function batched(callable $callback)
    {
        if (empty($this->batches)) {
            return collect();
        }

        return collect($this->batches)->filter(fn ($batch) => $callback($batch));
    }

    /**
     * Determine if there are any stored commands for a given class.
	 * 确定是否有任何针对给定类的存储命令
     *
     * @param  string  $command
     * @return bool
     */
    public function hasDispatched($command)
    {
        return isset($this->commands[$command]) && ! empty($this->commands[$command]);
    }

    /**
     * Determine if there are any stored commands for a given class.
	 * 确定是否有任何针对给定类的存储命令
     *
     * @param  string  $command
     * @return bool
     */
    public function hasDispatchedSync($command)
    {
        return isset($this->commandsSync[$command]) && ! empty($this->commandsSync[$command]);
    }

    /**
     * Determine if there are any stored commands for a given class.
	 * 确定是否有任何针对给定类的存储命令
     *
     * @param  string  $command
     * @return bool
     */
    public function hasDispatchedAfterResponse($command)
    {
        return isset($this->commandsAfterResponse[$command]) && ! empty($this->commandsAfterResponse[$command]);
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
        if ($this->shouldFakeJob($command)) {
            $this->commands[get_class($command)][] = $command;
        } else {
            return $this->dispatcher->dispatch($command);
        }
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
        if ($this->shouldFakeJob($command)) {
            $this->commandsSync[get_class($command)][] = $command;
        } else {
            return $this->dispatcher->dispatchSync($command, $handler);
        }
    }

    /**
     * Dispatch a command to its appropriate handler in the current process.
	 * 将命令分派给当前进程中相应的处理程序
     *
     * @param  mixed  $command
     * @param  mixed  $handler
     * @return mixed
     */
    public function dispatchNow($command, $handler = null)
    {
        if ($this->shouldFakeJob($command)) {
            $this->commands[get_class($command)][] = $command;
        } else {
            return $this->dispatcher->dispatchNow($command, $handler);
        }
    }

    /**
     * Dispatch a command to its appropriate handler behind a queue.
	 * 将命令分派到队列后面相应的处理程序
     *
     * @param  mixed  $command
     * @return mixed
     */
    public function dispatchToQueue($command)
    {
        if ($this->shouldFakeJob($command)) {
            $this->commands[get_class($command)][] = $command;
        } else {
            return $this->dispatcher->dispatchToQueue($command);
        }
    }

    /**
     * Dispatch a command to its appropriate handler.
	 * 将命令分派给相应的处理程序
     *
     * @param  mixed  $command
     * @return mixed
     */
    public function dispatchAfterResponse($command)
    {
        if ($this->shouldFakeJob($command)) {
            $this->commandsAfterResponse[get_class($command)][] = $command;
        } else {
            return $this->dispatcher->dispatch($command);
        }
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

        return new PendingChainFake($this, $jobs->shift(), $jobs->toArray());
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
        return $this->batchRepository->find($batchId);
    }

    /**
     * Create a new batch of queueable jobs.
	 * 创建一批新的可排队作业
     *
     * @param  \Illuminate\Support\Collection|array  $jobs
     * @return \Illuminate\Bus\PendingBatch
     */
    public function batch($jobs)
    {
        return new PendingBatchFake($this, Collection::wrap($jobs));
    }

    /**
     * Dispatch an empty job batch for testing.
	 * 调度一个空的作业批处理以进行测试
     *
     * @param  string  $name
     * @return \Illuminate\Bus\Batch
     */
    public function dispatchFakeBatch($name = '')
    {
        return $this->batch([])->name($name)->dispatch();
    }

    /**
     * Record the fake pending batch dispatch.
	 * 记录假的待处理批发货
     *
     * @param  \Illuminate\Bus\PendingBatch  $pendingBatch
     * @return \Illuminate\Bus\Batch
     */
    public function recordPendingBatch(PendingBatch $pendingBatch)
    {
        $this->batches[] = $pendingBatch;

        return $this->batchRepository->store($pendingBatch);
    }

    /**
     * Determine if a command should be faked or actually dispatched.
	 * 确定是伪造命令还是实际分派命令
     *
     * @param  mixed  $command
     * @return bool
     */
    protected function shouldFakeJob($command)
    {
        if ($this->shouldDispatchCommand($command)) {
            return false;
        }

        if (empty($this->jobsToFake)) {
            return true;
        }

        return collect($this->jobsToFake)
            ->filter(function ($job) use ($command) {
                return $job instanceof Closure
                            ? $job($command)
                            : $job === get_class($command);
            })->isNotEmpty();
    }

    /**
     * Determine if a command should be dispatched or not.
	 * 确定是否应该分派命令
     *
     * @param  mixed  $command
     * @return bool
     */
    protected function shouldDispatchCommand($command)
    {
        return collect($this->jobsToDispatch)
            ->filter(function ($job) use ($command) {
                return $job instanceof Closure
                    ? $job($command)
                    : $job === get_class($command);
            })->isNotEmpty();
    }

    /**
     * Set the pipes commands should be piped through before dispatching.
	 * 设置调度前需要通过管道的命令
     *
     * @param  array  $pipes
     * @return $this
     */
    public function pipeThrough(array $pipes)
    {
        $this->dispatcher->pipeThrough($pipes);

        return $this;
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
        return $this->dispatcher->hasCommandHandler($command);
    }

    /**
     * Retrieve the handler for a command.
	 * 检索命令的处理程序
     *
     * @param  mixed  $command
     * @return mixed
     */
    public function getCommandHandler($command)
    {
        return $this->dispatcher->getCommandHandler($command);
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
        $this->dispatcher->map($map);

        return $this;
    }
}
