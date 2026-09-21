<?php
/**
 * Illuminate，契约，认证，密码代理工厂
 */

namespace Illuminate\Contracts\Auth;

interface PasswordBrokerFactory
{
    /**
     * Get a password broker instance by name.
	 * 得到密码代理实例通过名称
     *
     * @param  string|null  $name
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker($name = null);
}
