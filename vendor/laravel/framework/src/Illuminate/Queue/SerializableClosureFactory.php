<?php
/**
 * Illuminate，队列，可序列化的闭包工厂
 */

namespace Illuminate\Queue;

use Laravel\SerializableClosure\SerializableClosure;
use Opis\Closure\SerializableClosure as OpisSerializableClosure;

/**
 * @deprecated This class will be removed in Laravel 9.
 */
class SerializableClosureFactory
{
    /**
     * Creates a new serializable closure from the given closure.
	 * 从给定闭包创建一个新的可序列化闭包
     *
     * @param  \Closure  $closure
     * @return \Laravel\SerializableClosure\SerializableClosure
     */
    public static function make($closure)
    {
        if (\PHP_VERSION_ID < 70400) {
            return new OpisSerializableClosure($closure);
        }

        return new SerializableClosure($closure);
    }
}
