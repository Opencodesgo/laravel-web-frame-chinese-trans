<?php
/**
 * Illuminate，队列，呼叫队列闭包
 */

namespace Illuminate\Queue;

use Closure;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Laravel\SerializableClosure\SerializableClosure;
use ReflectionFunction;

class CallQueuedClosure implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The serializable Closure instance.
	 * 可序列化的闭包实例
     *
     * @var \Laravel\SerializableClosure\SerializableClosure
     */
    public $closure;

    /**
     * The callbacks that should be executed on failure.
	 * 失败时应该执行的回调
     *
     * @var array
     */
    public $failureCallbacks = [];

    /**
     * Indicate if the job should be deleted when models are missing.
	 * 指示当模型丢失时是否应该删除作业
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
	 * 创建新的作业实例
     *
     * @param  \Laravel\SerializableClosure\SerializableClosure  $closure
     * @return void
     */
    public function __construct($closure)
    {
        $this->closure = $closure;
    }

    /**
     * Create a new job instance.
	 * 创建一个新的作业实例
     *
     * @param  \Closure  $job
     * @return self
     */
    public static function create(Closure $job)
    {
        return new self(new SerializableClosure($job));
    }

    /**
     * Execute the job.
	 * 执行作业
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return void
     */
    public function handle(Container $container)
    {
        $container->call($this->closure->getClosure(), ['job' => $this]);
    }

    /**
     * Add a callback to be executed if the job fails.
	 * 添加一个回调，在作业失败时执行。
     *
     * @param  callable  $callback
     * @return $this
     */
    public function onFailure($callback)
    {
        $this->failureCallbacks[] = $callback instanceof Closure
                        ? new SerializableClosure($callback)
                        : $callback;

        return $this;
    }

    /**
     * Handle a job failure.
	 * 处理作业失败
     *
     * @param  \Throwable  $e
     * @return void
     */
    public function failed($e)
    {
        foreach ($this->failureCallbacks as $callback) {
            $callback($e);
        }
    }

    /**
     * Get the display name for the queued job.
	 * 获取排队作业的显示名称
     *
     * @return string
     */
    public function displayName()
    {
        $reflection = new ReflectionFunction($this->closure->getClosure());

        return 'Closure ('.basename($reflection->getFileName()).':'.$reflection->getStartLine().')';
    }
}
