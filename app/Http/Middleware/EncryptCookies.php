<?php
/**
 * app，Http，中间件，加密 Cookie
 */

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
	 * 不应加密的cookie的名称
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
