<?php
/**
 * Illuminate，支持，特征，反射闭环
 */

namespace Illuminate\Support\Traits;

use Closure;
use Illuminate\Support\Reflector;
use ReflectionFunction;
use RuntimeException;

trait ReflectsClosures
{
    /**
     * Get the class names / types of the parameters of the given Closure.
	 * 获取给定闭包参数的类名/类型
     *
     * @param  \Closure  $closure
     * @return array
     *
     * @throws \ReflectionException
     */
    protected function closureParameterTypes(Closure $closure)
    {
        $reflection = new ReflectionFunction($closure);

        return collect($reflection->getParameters())->mapWithKeys(function ($parameter) {
            if ($parameter->isVariadic()) {
                return [$parameter->getName() => null];
            }

            return [$parameter->getName() => Reflector::getParameterClassName($parameter)];
        })->all();
    }

    /**
     * Get the class name of the first parameter of the given Closure.
	 * 获取给定闭包的第一个参数的类名
     *
     * @param  \Closure  $closure
     * @return string
     *
     * @throws \ReflectionException|\RuntimeException
     */
    protected function firstClosureParameterType(Closure $closure)
    {
        $types = array_values($this->closureParameterTypes($closure));

        if (! $types) {
            throw new RuntimeException('The given Closure has no parameters.');		#给定的Closure没有参数
        }

        if ($types[0] === null) {
            throw new RuntimeException('The first parameter of the given Closure is missing a type hint.');
        }

        return $types[0];
    }
}
