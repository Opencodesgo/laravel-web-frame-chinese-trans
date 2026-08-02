<?php
/**
 * PhpParser，碰撞，适配器，单元测试，定时器
 */

/**
 * This file is part of Collision.
 *
 * (c) Nuno Maduro <enunomaduro@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace NunoMaduro\Collision\Adapters\Phpunit;

/**
 * @internal
 */
final class Timer
{
    /**
     * @var float
     */
    private $start;

    /**
     * Timer constructor.
	 * 计时器的构造函数
     */
    private function __construct(float $start)
    {
        $this->start = $start;
    }

    /**
     * Starts the timer.
	 * 启动计时器
     */
    public static function start(): Timer
    {
        return new self(microtime(true));
    }

    /**
     * Returns the elapsed time in microseconds.
	 * 返回以微秒为单位的运行时间
     */
    public function result(): float
    {
        return microtime(true) - $this->start;
    }
}
