<?php
/**
 * Illuminate, 支持, 时间盒
 */

namespace Illuminate\Support;

class Timebox
{
    /**
     * Indicates if the timebox is allowed to return early.
	 * 指示是否允许时间框提前返回
     *
     * @var bool
     */
    public $earlyReturn = false;

    /**
     * Invoke the given callback within the specified timebox minimum.
	 * 在指定的最小时间框内调用给定的回调
     *
     * @template TCallReturnType
     *
     * @param  (callable($this): TCallReturnType)  $callback
     * @param  int  $microseconds
     * @return TCallReturnType
     */
    public function call(callable $callback, int $microseconds)
    {
        $start = microtime(true);

        $result = $callback($this);

        $remainder = intval($microseconds - ((microtime(true) - $start) * 1000000));

        if (! $this->earlyReturn && $remainder > 0) {
            $this->usleep($remainder);
        }

        return $result;
    }

    /**
     * Indicate that the timebox can return early.
	 * 表示时间盒可以提前返回
     *
     * @return $this
     */
    public function returnEarly()
    {
        $this->earlyReturn = true;

        return $this;
    }

    /**
     * Indicate that the timebox cannot return early.
	 * 指定时间盒不能提前返回
     *
     * @return $this
     */
    public function dontReturnEarly()
    {
        $this->earlyReturn = false;

        return $this;
    }

    /**
     * Sleep for the specified number of microseconds.
	 * 休眠指定的微秒数
     *
     * @param  int  $microseconds
     * @return void
     */
    protected function usleep(int $microseconds)
    {
        usleep($microseconds);
    }
}
