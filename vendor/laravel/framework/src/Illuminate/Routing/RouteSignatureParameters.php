<?php
/**
 * Illuminate，路由，路由签名参数
 */

namespace Illuminate\Routing;

use Illuminate\Support\Reflector;
use Illuminate\Support\Str;
use ReflectionFunction;
use ReflectionMethod;

class RouteSignatureParameters
{
    /**
     * Extract the route action's signature parameters.
	 * 提取路由动作的签名参数
     *
     * @param  array  $action
     * @param  array  $conditions
     * @return array
     */
    public static function fromAction(array $action, $conditions = [])
    {
        $callback = RouteAction::containsSerializedClosure($action)
                        ? unserialize($action['uses'])->getClosure()
                        : $action['uses'];

        $parameters = is_string($callback)
                        ? static::fromClassMethodString($callback)
                        : (new ReflectionFunction($callback))->getParameters();

        return match (true) {
            ! empty($conditions['subClass']) => array_filter($parameters, fn ($p) => Reflector::isParameterSubclassOf($p, $conditions['subClass'])),
            ! empty($conditions['backedEnum']) => array_filter($parameters, fn ($p) => Reflector::isParameterBackedEnumWithStringBackingType($p)),
            default => $parameters,
        };
    }

    /**
     * Get the parameters for the given class / method by string.
	 * 通过字符串获取给定类/方法的参数
     *
     * @param  string  $uses
     * @return array
     */
    protected static function fromClassMethodString($uses)
    {
        [$class, $method] = Str::parseCallback($uses);

        if (! method_exists($class, $method) && Reflector::isCallable($class, $method)) {
            return [];
        }

        return (new ReflectionMethod($class, $method))->getParameters();
    }
}
