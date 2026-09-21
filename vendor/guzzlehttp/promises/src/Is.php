<?php
/**
 * GuzzleHttp，Promise，Is
 */

declare(strict_types=1);

namespace GuzzleHttp\Promise;

final class Is
{
    /**
     * Returns true if a promise is pending.
	 * 如果承诺正在等待，则返回true。
     */
    public static function pending(PromiseInterface $promise): bool
    {
        return $promise->getState() === PromiseInterface::PENDING;
    }

    /**
     * Returns true if a promise is fulfilled or rejected.
	 * 如果承诺被履行或拒绝，返回true。
     */
    public static function settled(PromiseInterface $promise): bool
    {
        return $promise->getState() !== PromiseInterface::PENDING;
    }

    /**
     * Returns true if a promise is fulfilled.
	 * 如果实现了承诺，则返回true。
     */
    public static function fulfilled(PromiseInterface $promise): bool
    {
        return $promise->getState() === PromiseInterface::FULFILLED;
    }

    /**
     * Returns true if a promise is rejected.
	 * 如果承诺被拒绝，返回true。
     */
    public static function rejected(PromiseInterface $promise): bool
    {
        return $promise->getState() === PromiseInterface::REJECTED;
    }
}
