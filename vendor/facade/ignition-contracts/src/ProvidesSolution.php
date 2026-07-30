<?php
/**
 * Facade，点火契约，提供方案
 */

namespace Facade\IgnitionContracts;

interface ProvidesSolution
{
    public function getSolution(): Solution;
}
