<?php
/**
 * Illuminate，邮件，事件，信息传送中
 */

namespace Illuminate\Mail\Events;

use Symfony\Component\Mime\Email;

class MessageSending
{
    /**
     * The Symfony Email instance.
	 * Symfony Email实例
     *
     * @var \Symfony\Component\Mime\Email
     */
    public $message;

    /**
     * The message data.
	 * 消息数据
     *
     * @var array
     */
    public $data;

    /**
     * Create a new event instance.
	 * 创建一个新的事件实例
     *
     * @param  \Symfony\Component\Mime\Email  $message
     * @param  array  $data
     * @return void
     */
    public function __construct(Email $message, array $data = [])
    {
        $this->data = $data;
        $this->message = $message;
    }
}
