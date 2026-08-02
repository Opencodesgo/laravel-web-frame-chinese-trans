<?php
/**
 * Facade，FlareClient，Http，异常，缺失参数
 */

namespace Facade\FlareClient\Http\Exceptions;

use Exception;

class MissingParameter extends Exception
{
    public static function create(string $parameterName)
    {
        return new static("`$parameterName` is a required parameter");
    }
}
