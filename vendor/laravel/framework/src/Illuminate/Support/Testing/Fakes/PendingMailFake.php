<?php
/**
 * Illuminate，支持，测试，假装，待处理邮件假装
 */

namespace Illuminate\Support\Testing\Fakes;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Mail\PendingMail;

class PendingMailFake extends PendingMail
{
    /**
     * Create a new instance.
	 * 创建新的实例
     *
     * @param  \Illuminate\Support\Testing\Fakes\MailFake  $mailer
     * @return void
     */
    public function __construct($mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Send a new mailable message instance.
	 * 发送一个新的可邮件消息实例
     *
     * @param  \Illuminate\Contracts\Mail\Mailable  $mailable
     * @return mixed
     */
    public function send(Mailable $mailable)
    {
        return $this->mailer->send($this->fill($mailable));
    }

    /**
     * Send a mailable message immediately.
	 * 立即发送可发送的消息
     *
     * @param  \Illuminate\Contracts\Mail\Mailable  $mailable
     * @return mixed
     *
     * @deprecated Use send() instead.
     */
    public function sendNow(Mailable $mailable)
    {
        return $this->send($mailable);
    }

    /**
     * Push the given mailable onto the queue.
	 * 将给定的可邮件推送到队列中
     *
     * @param  \Illuminate\Contracts\Mail\Mailable  $mailable
     * @return mixed
     */
    public function queue(Mailable $mailable)
    {
        return $this->mailer->queue($this->fill($mailable));
    }
}
