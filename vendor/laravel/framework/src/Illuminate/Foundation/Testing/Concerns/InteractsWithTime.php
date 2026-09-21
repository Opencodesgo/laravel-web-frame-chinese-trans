<?php
/**
 * Illuminate，基础，测试，问题，与时间交互
 */

namespace Illuminate\Foundation\Testing\Concerns;

use Illuminate\Foundation\Testing\Wormhole;
use Illuminate\Support\Carbon;

trait InteractsWithTime
{
    /**
     * Freeze time.
	 * 冻结时间
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function freezeTime($callback = null)
    {
        return $this->travelTo(Carbon::now(), $callback);
    }

    /**
     * Freeze time at the beginning of the current second.
	 * 将时间冻结在当前秒的开始
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function freezeSecond($callback = null)
    {
        return $this->travelTo(Carbon::now()->startOfSecond(), $callback);
    }

    /**
     * Begin travelling to another time.
	 * 开始旅行到另一个时间
     *
     * @param  int  $value
     * @return \Illuminate\Foundation\Testing\Wormhole
     */
    public function travel($value)
    {
        return new Wormhole($value);
    }

    /**
     * Travel to another time.
	 * 旅行到另一个时代
     *
     * @param  \DateTimeInterface|\Closure|\Illuminate\Support\Carbon|string|bool|null  $date
     * @param  callable|null  $callback
     * @return mixed
     */
    public function travelTo($date, $callback = null)
    {
        Carbon::setTestNow($date);

        if ($callback) {
            return tap($callback($date), function () {
                Carbon::setTestNow();
            });
        }
    }

    /**
     * Travel back to the current time.
	 * 回到当前的时间
     *
     * @return \DateTimeInterface
     */
    public function travelBack()
    {
        return Wormhole::back();
    }
}
