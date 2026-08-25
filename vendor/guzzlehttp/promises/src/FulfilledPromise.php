<?php
/**
 * GuzzleHttp，许诺，兑现诺言
 */

declare(strict_types=1);

namespace GuzzleHttp\Promise;

/**
 * A promise that has been fulfilled.
 * 一个已经实现的承诺。
 *
 * Thenning off of this promise will invoke the onFulfilled callback
 * immediately and ignore other callbacks.
 * 取消此承诺将立即调用 onFulfilled 回调函数，并忽略其他回调。
 *
 * @final
 */
class FulfilledPromise implements PromiseInterface
{
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        if (is_object($value) && method_exists($value, 'then')) {
            throw new \InvalidArgumentException(
                'You cannot create a FulfilledPromise with a promise.'
            );
        }

        $this->value = $value;
    }

    public function then(
        ?callable $onFulfilled = null,
        ?callable $onRejected = null
    ): PromiseInterface {
        // Return itself if there is no onFulfilled function.
		// 如果没有oncompleted函数，则返回自身。
        if (!$onFulfilled) {
            return $this;
        }

        $queue = Utils::queue();
        $p = new Promise([$queue, 'run']);
        $value = $this->value;
        $queue->add(static function () use ($p, $value, $onFulfilled): void {
            if (Is::pending($p)) {
                try {
                    $p->resolve($onFulfilled($value));
                } catch (\Throwable $e) {
                    $p->reject($e);
                }
            }
        });

        return $p;
    }

    public function otherwise(callable $onRejected): PromiseInterface
    {
        return $this->then(null, $onRejected);
    }

    public function wait(bool $unwrap = true)
    {
        return $unwrap ? $this->value : null;
    }

    public function getState(): string
    {
        return self::FULFILLED;
    }

    public function resolve($value): void
    {
        if ($value !== $this->value) {
            throw new \LogicException('Cannot resolve a fulfilled promise');
        }
    }

    public function reject($reason): void
    {
        throw new \LogicException('Cannot reject a fulfilled promise');
    }

    public function cancel(): void
    {
        // pass
    }
}
