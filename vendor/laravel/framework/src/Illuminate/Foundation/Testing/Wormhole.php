<?php
/**
 * Illuminate，基础，测试，虫洞
 */

namespace Illuminate\Foundation\Testing;

use Illuminate\Support\Carbon;

class Wormhole
{
    /**
     * The amount of time to travel.
	 * 旅行的时间
     *
     * @var int
     */
    public $value;

    /**
     * Create a new wormhole instance.
	 * 创建一个新的虫洞实例
     *
     * @param  int  $value
     * @return void
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Travel forward the given number of milliseconds.
	 * 向前移动给定的毫秒数
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function millisecond($callback = null)
    {
        return $this->milliseconds($callback);
    }

    /**
     * Travel forward the given number of milliseconds.
	 * 向前移动给定的毫秒数
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function milliseconds($callback = null)
    {
        Carbon::setTestNow(Carbon::now()->addMilliseconds($this->value));

        return $this->handleCallback($callback);
    }

    /**
     * Travel forward the given number of seconds.
	 * 向前移动给定的秒数
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function second($callback = null)
    {
        return $this->seconds($callback);
    }

    /**
     * Travel forward the given number of seconds.
	 * 向前移动给定的秒数
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function seconds($callback = null)
    {
        Carbon::setTestNow(Carbon::now()->addSeconds($this->value));

        return $this->handleCallback($callback);
    }

    /**
     * Travel forward the given number of minutes.
	 * 向前移动给定的分钟数
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function minute($callback = null)
    {
        return $this->minutes($callback);
    }

    /**
     * Travel forward the given number of minutes.
	 * 向前移动给定的分钟数
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function minutes($callback = null)
    {
        Carbon::setTestNow(Carbon::now()->addMinutes($this->value));

        return $this->handleCallback($callback);
    }

    /**
     * Travel forward the given number of hours.
	 * 向前移动给定的小时数
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function hour($callback = null)
    {
        return $this->hours($callback);
    }

    /**
     * Travel forward the given number of hours.
	 * 向前移动给定的小时数
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function hours($callback = null)
    {
        Carbon::setTestNow(Carbon::now()->addHours($this->value));

        return $this->handleCallback($callback);
    }

    /**
     * Travel forward the given number of days.
	 * 向前移动给定的天数
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function day($callback = null)
    {
        return $this->days($callback);
    }

    /**
     * Travel forward the given number of days.
	 * 向前移动给定的天数
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function days($callback = null)
    {
        Carbon::setTestNow(Carbon::now()->addDays($this->value));

        return $this->handleCallback($callback);
    }

    /**
     * Travel forward the given number of weeks.
	 * 向前移动给定的周数
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function week($callback = null)
    {
        return $this->weeks($callback);
    }

    /**
     * Travel forward the given number of weeks.
	 * 向前移动给定的周数
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function weeks($callback = null)
    {
        Carbon::setTestNow(Carbon::now()->addWeeks($this->value));

        return $this->handleCallback($callback);
    }

    /**
     * Travel forward the given number of months.
	 * 向前行进给定的月数
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function month($callback = null)
    {
        return $this->months($callback);
    }

    /**
     * Travel forward the given number of months.
	 * 向前行进给定的月数
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function months($callback = null)
    {
        Carbon::setTestNow(Carbon::now()->addMonths($this->value));

        return $this->handleCallback($callback);
    }

    /**
     * Travel forward the given number of years.
	 * 向前旅行给定的年数
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function year($callback = null)
    {
        return $this->years($callback);
    }

    /**
     * Travel forward the given number of years.
	 * 向前旅行给定的年数
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function years($callback = null)
    {
        Carbon::setTestNow(Carbon::now()->addYears($this->value));

        return $this->handleCallback($callback);
    }

    /**
     * Travel back to the current time.
	 * 回到现在的时间
     *
     * @return \DateTimeInterface
     */
    public static function back()
    {
        Carbon::setTestNow();

        return Carbon::now();
    }

    /**
     * Handle the given optional execution callback.
	 * 处理给定的可选执行回调
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    protected function handleCallback($callback)
    {
        if ($callback) {
            return tap($callback(), function () {
                Carbon::setTestNow();
            });
        }
    }
}
