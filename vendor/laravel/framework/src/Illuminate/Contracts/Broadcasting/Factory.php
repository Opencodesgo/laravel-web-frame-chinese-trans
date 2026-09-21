<?php
/**
 * Illuminate，契约，广播，工厂
 */

namespace Illuminate\Contracts\Broadcasting;

interface Factory
{
    /**
     * Get a broadcaster implementation by name.
	 * 得到广播器实现通过名称
     *
     * @param  string|null  $name
     * @return \Illuminate\Contracts\Broadcasting\Broadcaster
     */
    public function connection($name = null);
}
