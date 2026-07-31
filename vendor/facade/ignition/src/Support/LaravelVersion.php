<?php
/**
 * Facade，Ignition，支持，Laravel 版本
 */

namespace Facade\Ignition\Support;

class LaravelVersion
{
    public static function major()
    {
        return substr(app()->version(), 0, 1);
    }
}
