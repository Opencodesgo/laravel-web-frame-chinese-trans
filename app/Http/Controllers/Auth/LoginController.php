<?php
/**
 * App，Http，控制器，授权，登录控制器
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller  登录控制器
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
	| 这个控制器负责为应用程序进行身份验证,并将其重新定向到主屏幕。
	| 控制器使用一个特性来方便地向应用程序提供它的功能。
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
	 * 登录之后的重定向
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     * 创建新的控制器实例
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
