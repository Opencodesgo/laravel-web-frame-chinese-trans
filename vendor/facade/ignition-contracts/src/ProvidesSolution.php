<?php
/**
 * 门面，Ignition 契约，提供解决方案
 */

namespace Facade\IgnitionContracts;

interface ProvidesSolution
{
    public function getSolution(): Solution;
}
