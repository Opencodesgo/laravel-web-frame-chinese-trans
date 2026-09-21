<?php
/**
 * Illuminate，总线，待处理的批处理
 */

namespace Illuminate\Bus;

use Closure;
use Illuminate\Bus\Events\BatchDispatched;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Laravel\SerializableClosure\SerializableClosure;
use Throwable;

class PendingBatch
{
    /**
     * The IoC container instance.
	 * IoC容器实例
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * The batch name.
	 * 批量名称
     *
     * @var string
     */
    public $name = '';

    /**
     * The jobs that belong to the batch.
	 * 属于批处理的作业
     *
     * @var \Illuminate\Support\Collection
     */
    public $jobs;

    /**
     * The batch options.
	 * 批选项
     *
     * @var array
     */
    public $options = [];

    /**
     * Create a new pending batch instance.
	 * 创建一个新的挂起批处理实例
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @param  \Illuminate\Support\Collection  $jobs
     * @return void
     */
    public function __construct(Container $container, Collection $jobs)
    {
        $this->container = $container;
        $this->jobs = $jobs;
    }

    /**
     * Add jobs to the batch.
	 * 添加任务到批
     *
     * @param  iterable|object|array  $jobs
     * @return $this
     */
    public function add($jobs)
    {
        $jobs = is_iterable($jobs) ? $jobs : Arr::wrap($jobs);

        foreach ($jobs as $job) {
            $this->jobs->push($job);
        }

        return $this;
    }

    /**
     * Add a callback to be executed after all jobs in the batch have executed successfully.
	 * 添加回调，在批处理中的所有作业成功执行后执行。
     *
     * @param  callable  $callback
     * @return $this
     */
    public function then($callback)
    {
        $this->options['then'][] = $callback instanceof Closure
                        ? new SerializableClosure($callback)
                        : $callback;

        return $this;
    }

    /**
     * Get the "then" callbacks that have been registered with the pending batch.
	 * 获取已在挂起批处理中注册的"then"回调
     *
     * @return array
     */
    public function thenCallbacks()
    {
        return $this->options['then'] ?? [];
    }

    /**
     * Add a callback to be executed after the first failing job in the batch.
	 * 添加一个回调，在批处理中第一个失败的作业后执行。
     *
     * @param  callable  $callback
     * @return $this
     */
    public function catch($callback)
    {
        $this->options['catch'][] = $callback instanceof Closure
                    ? new SerializableClosure($callback)
                    : $callback;

        return $this;
    }

    /**
     * Get the "catch" callbacks that have been registered with the pending batch.
	 * 获取已在挂起批处理中注册的"catch"回调
     *
     * @return array
     */
    public function catchCallbacks()
    {
        return $this->options['catch'] ?? [];
    }

    /**
     * Add a callback to be executed after the batch has finished executing.
	 * 添加一个回调，将在批处理完成执行后执行。
     *
     * @param  callable  $callback
     * @return $this
     */
    public function finally($callback)
    {
        $this->options['finally'][] = $callback instanceof Closure
                    ? new SerializableClosure($callback)
                    : $callback;

        return $this;
    }

    /**
     * Get the "finally" callbacks that have been registered with the pending batch.
	 * 获取已在挂起批处理中注册的"finally"回调
     *
     * @return array
     */
    public function finallyCallbacks()
    {
        return $this->options['finally'] ?? [];
    }

    /**
     * Indicate that the batch should not be cancelled when a job within the batch fails.
	 * 指示当批处理中的作业失败时不应取消批处理
     *
     * @param  bool  $allowFailures
     * @return $this
     */
    public function allowFailures($allowFailures = true)
    {
        $this->options['allowFailures'] = $allowFailures;

        return $this;
    }

    /**
     * Determine if the pending batch allows jobs to fail without cancelling the batch.
	 * 确定挂起的批处理是否允许作业失败而不取消批处理
     *
     * @return bool
     */
    public function allowsFailures()
    {
        return Arr::get($this->options, 'allowFailures', false) === true;
    }

    /**
     * Set the name for the batch.
	 * 设置批处理的名称
     *
     * @param  string  $name
     * @return $this
     */
    public function name(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Specify the queue connection that the batched jobs should run on.
	 * 指定批处理作业应该运行的队列连接
     *
     * @param  string  $connection
     * @return $this
     */
    public function onConnection(string $connection)
    {
        $this->options['connection'] = $connection;

        return $this;
    }

    /**
     * Get the connection used by the pending batch.
	 * 获取挂起批处理使用的连接
     *
     * @return string|null
     */
    public function connection()
    {
        return $this->options['connection'] ?? null;
    }

    /**
     * Specify the queue that the batched jobs should run on.
	 * 指定批处理作业应该在其上运行的队列
     *
     * @param  string  $queue
     * @return $this
     */
    public function onQueue(string $queue)
    {
        $this->options['queue'] = $queue;

        return $this;
    }

    /**
     * Get the queue used by the pending batch.
	 * 获取挂起批处理使用的队列
     *
     * @return string|null
     */
    public function queue()
    {
        return $this->options['queue'] ?? null;
    }

    /**
     * Add additional data into the batch's options array.
	 * 将其他数据添加到批处理的选项数组中
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function withOption(string $key, $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * Dispatch the batch.
	 * 调度批次
     *
     * @return \Illuminate\Bus\Batch
     *
     * @throws \Throwable
     */
    public function dispatch()
    {
        $repository = $this->container->make(BatchRepository::class);

        try {
            $batch = $repository->store($this);

            $batch = $batch->add($this->jobs);
        } catch (Throwable $e) {
            if (isset($batch)) {
                $repository->delete($batch->id);
            }

            throw $e;
        }

        $this->container->make(EventDispatcher::class)->dispatch(
            new BatchDispatched($batch)
        );

        return $batch;
    }

    /**
     * Dispatch the batch after the response is sent to the browser.
	 * 在将响应发送到浏览器后，分派该批处理。
     *
     * @return \Illuminate\Bus\Batch
     */
    public function dispatchAfterResponse()
    {
        $repository = $this->container->make(BatchRepository::class);

        $batch = $repository->store($this);

        if ($batch) {
            $this->container->terminating(function () use ($batch) {
                $this->dispatchExistingBatch($batch);
            });
        }

        return $batch;
    }

    /**
     * Dispatch an existing batch.
	 * 调度一个存在的批处理
     *
     * @param  \Illuminate\Bus\Batch  $batch
     * @return void
     *
     * @throws \Throwable
     */
    protected function dispatchExistingBatch($batch)
    {
        try {
            $batch = $batch->add($this->jobs);
        } catch (Throwable $e) {
            if (isset($batch)) {
                $batch->delete();
            }

            throw $e;
        }

        $this->container->make(EventDispatcher::class)->dispatch(
            new BatchDispatched($batch)
        );
    }
}
