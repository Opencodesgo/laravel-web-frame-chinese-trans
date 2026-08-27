<?php
/**
 * Illuminate，契约，认证，必须验证电子邮件
 */

namespace Illuminate\Contracts\Auth;

interface MustVerifyEmail
{
    /**
     * Determine if the user has verified their email address.
	 * 确定用户是否已验证了他们的邮件地址
     *
     * @return bool
     */
    public function hasVerifiedEmail();

    /**
     * Mark the given user's email as verified.
	 * 将给定用户的电子邮件标记为已验证
     *
     * @return bool
     */
    public function markEmailAsVerified();

    /**
     * Send the email verification notification.
	 * 发送邮件验证通知
     *
     * @return void
     */
    public function sendEmailVerificationNotification();

    /**
     * Get the email address that should be used for verification.
	 * 获取应该用于验证的电子邮件地址
     *
     * @return string
     */
    public function getEmailForVerification();
}
