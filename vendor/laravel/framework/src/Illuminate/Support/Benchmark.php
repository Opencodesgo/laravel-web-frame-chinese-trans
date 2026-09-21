<?php
/**
 * Illuminate, 支持, 基准
 */

namespace Illuminate\Support;

use Closure;

class Benchmark
{
    /**
     * Measure a callable or array of callables over the given number of iterations.
	 * 在给定的迭代次数上度量一个可调用对象或可调用对象数组
     *
     * @param  \Closure|array  $benchmarkables
     * @param  int  $iterations
     * @return array|float
     */
    public static function measure(Closure|array $benchmarkables, int $iterations = 1): array|float
    {
        return collect(Arr::wrap($benchmarkables))->map(function ($callback) use ($iterations) {
            return collect(range(1, $iterations))->map(function () use ($callback) {
                gc_collect_cycles();

                $start = hrtime(true);

                $callback();

                return (hrtime(true) - $start) / 1000000;
            })->average();
        })->when(
            $benchmarkables instanceof Closure,
            fn ($c) => $c->first(),
            fn ($c) => $c->all(),
        );
    }

    /**
     * Measure a callable or array of callables over the given number of iterations, then dump and die.
	 * 在给定的迭代次数上度量一个可调用对象或可调用对象数组，然后转储并死亡。
     *
     * @param  \Closure|array  $benchmarkables
     * @param  int  $iterations
     * @return never
     */
    public static function dd(Closure|array $benchmarkables, int $iterations = 1): void
    {
        $result = collect(static::measure(Arr::wrap($benchmarkables), $iterations))
            ->map(fn ($average) => number_format($average, 3).'ms')
            ->when($benchmarkables instanceof Closure, fn ($c) => $c->first(), fn ($c) => $c->all());

        dd($result);
    }
}
