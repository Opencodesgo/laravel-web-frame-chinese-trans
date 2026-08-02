<?php
/**
 * Illuminate，契约，邮件，工厂
 */

namespace Illuminate\Contracts\Mail;

interface Factory
{
    /**
     * Get a mailer instance by name.
	 * 按名称获取邮件实例
     *
     * @param  string|null  $name
     * @return \Illuminate\Contracts\Mail\Mailer
     */
    public function mailer($name = null);
}
