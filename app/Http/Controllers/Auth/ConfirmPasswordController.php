<?php
/**
 * App，Http，控制器，授权，确认密码控制器
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ConfirmsPasswords;

class ConfirmPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Confirm Password Controller   确认密码控制器
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password confirmations and
    | uses a simple trait to include the behavior. You're free to explore
    | this trait and override any functions that require customization.
	| 这个控制器负责处理密码确认,并使用一个简单的特性来包括行为。
	| 您可以自由地探索此特性并覆盖任何需要定制的功能。
    |
    */

    use ConfirmsPasswords;

    /**
     * Where to redirect users when the intended url fails.
	 * 在目标url失败时重定向用户
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
        $this->middleware('auth');
    }
}
