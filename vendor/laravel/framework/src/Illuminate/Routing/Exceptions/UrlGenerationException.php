<?php
/**
 * Illuminate，路由，异常，Url生成异常
 */

namespace Illuminate\Routing\Exceptions;

use Exception;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

class UrlGenerationException extends Exception
{
    /**
     * Create a new exception for missing route parameters.
	 * 为丢失的路由参数创建一个新的异常
     *
     * @param  \Illuminate\Routing\Route  $route
     * @param  array  $parameters
     * @return static
     */
    public static function forMissingParameters(Route $route, array $parameters = [])
    {
        $parameterLabel = Str::plural('parameter', count($parameters));

        $message = sprintf(
            'Missing required %s for [Route: %s] [URI: %s]',
            $parameterLabel,
            $route->getName(),
            $route->uri()
        );

        if (count($parameters) > 0) {
            $message .= sprintf(' [Missing %s: %s]', $parameterLabel, implode(', ', $parameters));
        }

        $message .= '.';

        return new static($message);
    }
}
