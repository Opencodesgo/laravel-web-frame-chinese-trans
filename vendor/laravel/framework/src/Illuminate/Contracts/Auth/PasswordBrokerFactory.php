<?php
/**
 * Illuminate，契约，认证，密码代理工厂
 */

namespace Illuminate\Contracts\Auth;

interface PasswordBrokerFactory
{
    /**
     * Get a password broker instance by name.
	 * 按名称获取密码代理实例
     *
     * @param  string|null  $name
     * @return mixed
     */
    public function broker($name = null);
}
