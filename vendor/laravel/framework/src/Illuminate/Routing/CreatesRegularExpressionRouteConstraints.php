<?php
/**
 * Illuminate，路由，创建正则表达式路由约束
 */

namespace Illuminate\Routing;

use Illuminate\Support\Arr;

trait CreatesRegularExpressionRouteConstraints
{
    /**
     * Specify that the given route parameters must be alphabetic.
	 * 指定给定的路由参数必须是字母顺序
     *
     * @param  array|string  $parameters
     * @return $this
     */
    public function whereAlpha($parameters)
    {
        return $this->assignExpressionToParameters($parameters, '[a-zA-Z]+');
    }

    /**
     * Specify that the given route parameters must be alphanumeric.
	 * 指定给定的路由参数必须是字母数字
     *
     * @param  array|string  $parameters
     * @return $this
     */
    public function whereAlphaNumeric($parameters)
    {
        return $this->assignExpressionToParameters($parameters, '[a-zA-Z0-9]+');
    }

    /**
     * Specify that the given route parameters must be numeric.
	 * 指定给定的路由参数必须是数字
     *
     * @param  array|string  $parameters
     * @return $this
     */
    public function whereNumber($parameters)
    {
        return $this->assignExpressionToParameters($parameters, '[0-9]+');
    }

    /**
     * Specify that the given route parameters must be UUIDs.
	 * 指定给定的路由参数必须是uuid
     *
     * @param  array|string  $parameters
     * @return $this
     */
    public function whereUuid($parameters)
    {
        return $this->assignExpressionToParameters($parameters, '[\da-fA-F]{8}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{12}');
    }

    /**
     * Apply the given regular expression to the given parameters.
	 * 将给定的正则表达式应用于给定的参数
     *
     * @param  array|string  $parameters
     * @param  string  $expression
     * @return $this
     */
    protected function assignExpressionToParameters($parameters, $expression)
    {
        return $this->where(collect(Arr::wrap($parameters))
                    ->mapWithKeys(function ($parameter) use ($expression) {
                        return [$parameter => $expression];
                    })->all());
    }
}
