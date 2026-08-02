<?php
/**
 * Facade，IgnitionContracts，提供解决方案
 */

namespace Facade\IgnitionContracts;

interface ProvidesSolution
{
    public function getSolution(): Solution;
}
