<?php
/**
 * Illuminate，基础，异常，可报告的处理程序
 */

namespace Illuminate\Foundation\Exceptions;

use Illuminate\Support\Traits\ReflectsClosures;
use Throwable;

class ReportableHandler
{
    use ReflectsClosures;

    /**
     * The underlying callback.
	 * 底层回调
     *
     * @var callable
     */
    protected $callback;

    /**
     * Indicates if reporting should stop after invoking this handler.
	 * 指示在调用此处理程序后报告是否应停止
     *
     * @var bool
     */
    protected $shouldStop = false;

    /**
     * Create a new reportable handler instance.
	 * 创建一个新的可报告处理程序实例
     *
     * @param  callable  $callback
     * @return void
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Invoke the handler.
	 * 调用处理程序
     *
     * @param  \Throwable  $e
     * @return bool
     */
    public function __invoke(Throwable $e)
    {
        $result = call_user_func($this->callback, $e);

        if ($result === false) {
            return false;
        }

        return ! $this->shouldStop;
    }

    /**
     * Determine if the callback handles the given exception.
	 * 确定回调是否处理给定的异常
     *
     * @param  \Throwable  $e
     * @return bool
     */
    public function handles(Throwable $e)
    {
        foreach ($this->firstClosureParameterTypes($this->callback) as $type) {
            if (is_a($e, $type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Indicate that report handling should stop after invoking this callback.
	 * 指示报表处理应在调用此回调后停止
     *
     * @return $this
     */
    public function stop()
    {
        $this->shouldStop = true;

        return $this;
    }
}
