<?php
/**
 * Spatie，Ignition，契约，提供者方案
 */

namespace Spatie\Ignition\Contracts;

/**
 * Interface to be used on exceptions that provide their own solution.
 * 接口，用于提供自身解决方案的异常。
 */
interface ProvidesSolution
{
    public function getSolution(): Solution;
}
