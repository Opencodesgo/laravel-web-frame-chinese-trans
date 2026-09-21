<?php
/**
 * Illuminate，契约，认证，可认证的
 */

namespace Illuminate\Contracts\Auth;

interface Authenticatable
{
    /**
     * Get the name of the unique identifier for the user.
	 * 获得用户的唯一标识符的名称
     *
     * @return string
     */
    public function getAuthIdentifierName();

    /**
     * Get the unique identifier for the user.
	 * 获取用户的唯一标识符
     *
     * @return mixed
     */
    public function getAuthIdentifier();

    /**
     * Get the password for the user.
	 * 获取用户的密码
     *
     * @return string
     */
    public function getAuthPassword();

    /**
     * Get the token value for the "remember me" session.
	 * 获取"记住我"会话的令牌值
     *
     * @return string
     */
    public function getRememberToken();

    /**
     * Set the token value for the "remember me" session.
	 * 设置"记住我"会话的令牌值
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value);

    /**
     * Get the column name for the "remember me" token.
	 * 获取"记住我"令牌的列名
     *
     * @return string
     */
    public function getRememberTokenName();
}
