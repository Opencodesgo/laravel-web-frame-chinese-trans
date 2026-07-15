<?php
/**
 * App，Http，控制器，授权，验证控制器
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\VerifiesEmails;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller     Email验证控制器
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
	| 这个控制器负责处理最近注册了应用的任何电子邮件的验证用户。
	| 电子邮件也可能被重发，如果用户没有收到原始的电子邮件消息。
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
	 * 在验证后重定向用户
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     * 控制新的控制器实例
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }
}
