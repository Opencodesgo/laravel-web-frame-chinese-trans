<?php
/**
 * App，Http，控制器，授权，忘记密码控制器
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller     密码重置控制器
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
	| 这个控制器负责处理密码重置电子邮件,并包括一个特性,它可以帮助将这些通知从你的应用程序发送给你的用户。
	| 你可以自由地探索这个特质。
    |
    */

    use SendsPasswordResetEmails;
}
