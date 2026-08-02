<?php
/**
 * Dotenv，存储库，适配器，可用性接口
 */

namespace Dotenv\Repository\Adapter;

interface AvailabilityInterface
{
    /**
     * Determines if the adapter is supported.
	 * 确定适配器是否被支持
     *
     * @return bool
     */
    public function isSupported();
}
