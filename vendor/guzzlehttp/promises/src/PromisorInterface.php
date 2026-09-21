<?php
/**
 * GuzzleHttp，Promise，契约者接口
 */

declare(strict_types=1);

namespace GuzzleHttp\Promise;

/**
 * Interface used with classes that return a promise.
 * 与返回承诺的类一起使用的接口
 */
interface PromisorInterface
{
    /**
     * Returns a promise.
     */
    public function promise(): PromiseInterface;
}
