<?php
/**
 * Spatie，LaravelIgnition，支持，Laravel 版本
 */

namespace Spatie\LaravelIgnition\Support;

class LaravelVersion
{
    public static function major(): string
    {
        return explode('.', app()->version())[0];
    }
}
