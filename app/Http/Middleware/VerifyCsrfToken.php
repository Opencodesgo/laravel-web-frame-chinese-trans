<?php
/**
 * App，Http，中间件，验证 CSRF令牌
 */

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
	 * 应该从CSRF验证中排除的URI
     *
     * @var array
     */
    protected $except = [
        //
    ];
}
