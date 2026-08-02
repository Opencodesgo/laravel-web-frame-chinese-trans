<?php
/**
 * Illuminate，契约，支持，可延期的提供者
 */

namespace Illuminate\Contracts\Support;

interface DeferrableProvider
{
    /**
     * Get the services provided by the provider.
	 * 获取提供者提供的服务
     *
     * @return array
     */
    public function provides();
}
