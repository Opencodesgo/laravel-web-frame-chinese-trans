<?php
/**
 * Illuminate，邮件，事件，信息正在发送
 */

namespace Illuminate\Mail\Events;

class MessageSending
{
    /**
     * The Swift message instance.
	 * Swift消息实例
     *
     * @var \Swift_Message
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
     * @param  \Swift_Message  $message
     * @param  array  $data
     * @return void
     */
    public function __construct($message, $data = [])
    {
        $this->data = $data;
        $this->message = $message;
    }
}
