<?php
/**
 * Illuminate，路由，中间件，验证签名
 */

namespace Illuminate\Routing\Middleware;

use Closure;
use Illuminate\Routing\Exceptions\InvalidSignatureException;

class ValidateSignature
{
    /**
     * The names of the parameters that should be ignored.
	 * 应该忽略的参数的名称
     *
     * @var array<int, string>
     */
    protected $ignore = [
        //
    ];

    /**
     * Handle an incoming request.
	 * 处理传入请求
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $relative
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Routing\Exceptions\InvalidSignatureException
     */
    public function handle($request, Closure $next, $relative = null)
    {
        $ignore = property_exists($this, 'except') ? $this->except : $this->ignore;

        if ($request->hasValidSignatureWhileIgnoring($ignore, $relative !== 'relative')) {
            return $next($request);
        }

        throw new InvalidSignatureException;
    }
}
