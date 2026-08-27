<?php
/**
 * GuzzleHttp，Promise，Each
 */

declare(strict_types=1);

namespace GuzzleHttp\Promise;

final class Each
{
    /**
     * Given an iterator that yields promises or values, returns a promise that
     * is fulfilled with a null value when the iterator has been consumed or
     * the aggregate promise has been fulfilled or rejected.
	 * 给定一个返回 Promise 或值的迭代器，当迭代器被消费或聚合 Promise 被完成或拒绝时，返回一个以 null 值完成的 Promise。
     *
     * $onFulfilled is a function that accepts the fulfilled value, iterator
     * index, and the aggregate promise. The callback can invoke any necessary
     * side effects and choose to resolve or reject the aggregate if needed.
     *
     * $onRejected is a function that accepts the rejection reason, iterator
     * index, and the aggregate promise. The callback can invoke any necessary
     * side effects and choose to resolve or reject the aggregate if needed.
     *
     * @param mixed $iterable Iterator or array to iterate over.
     */
    public static function of(
        $iterable,
        ?callable $onFulfilled = null,
        ?callable $onRejected = null
    ): PromiseInterface {
        return (new EachPromise($iterable, [
            'fulfilled' => $onFulfilled,
            'rejected' => $onRejected,
        ]))->promise();
    }

    /**
     * Like of, but only allows a certain number of outstanding promises at any
     * given time.
	 * 没有的事，在一定时间内但只允许一定数量的未完成的承诺。
     *
     * $concurrency may be an integer or a function that accepts the number of
     * pending promises and returns a numeric concurrency limit value to allow
     * for dynamic a concurrency size.
     *
     * @param mixed        $iterable
     * @param int|callable $concurrency
     */
    public static function ofLimit(
        $iterable,
        $concurrency,
        ?callable $onFulfilled = null,
        ?callable $onRejected = null
    ): PromiseInterface {
        return (new EachPromise($iterable, [
            'fulfilled' => $onFulfilled,
            'rejected' => $onRejected,
            'concurrency' => $concurrency,
        ]))->promise();
    }

    /**
     * Like limit, but ensures that no promise in the given $iterable argument
     * is rejected. If any promise is rejected, then the aggregate promise is
     * rejected with the encountered rejection.
	 * 类似于限制，但确保在给定的$iterable参数中没有承诺被拒绝。
     *
     * @param mixed        $iterable
     * @param int|callable $concurrency
     */
    public static function ofLimitAll(
        $iterable,
        $concurrency,
        ?callable $onFulfilled = null
    ): PromiseInterface {
        return self::ofLimit(
            $iterable,
            $concurrency,
            $onFulfilled,
            function ($reason, $idx, PromiseInterface $aggregate): void {
                $aggregate->reject($reason);
            }
        );
    }
}
