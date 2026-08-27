<?php
/**
 * Illuminate，邮件，事件，消息已发送
 */

namespace Illuminate\Mail\Events;

use Exception;
use Illuminate\Mail\SentMessage;

/**
 * @property \Symfony\Component\Mime\Email $message
 */
class MessageSent
{
    /**
     * The message that was sent.
	 * 发送的消息
     *
     * @var \Illuminate\Mail\SentMessage
     */
    public $sent;

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
     * @param  \Illuminate\Mail\SentMessage  $message
     * @param  array  $data
     * @return void
     */
    public function __construct(SentMessage $message, array $data = [])
    {
        $this->sent = $message;
        $this->data = $data;
    }

    /**
     * Get the serializable representation of the object.
	 * 获取对象的可序列化表示形式
     *
     * @return array
     */
    public function __serialize()
    {
        $hasAttachments = collect($this->message->getAttachments())->isNotEmpty();

        return $hasAttachments ? [
            'sent' => base64_encode(serialize($this->sent)),
            'data' => base64_encode(serialize($this->data)),
            'hasAttachments' => true,
        ] : [
            'sent' => $this->sent,
            'data' => $this->data,
            'hasAttachments' => false,
        ];
    }

    /**
     * Marshal the object from its serialized data.
	 * 从对象的序列化数据封送对象
     *
     * @param  array  $data
     * @return void
     */
    public function __unserialize(array $data)
    {
        if (isset($data['hasAttachments']) && $data['hasAttachments'] === true) {
            $this->sent = unserialize(base64_decode($data['sent']));
            $this->data = unserialize(base64_decode($data['data']));
        } else {
            $this->sent = $data['sent'];
            $this->data = $data['data'];
        }
    }

    /**
     * Dynamically get the original message.
	 * 动态获取原始消息
     *
     * @param  string  $key
     * @return mixed
     *
     * @throws \Exception
     */
    public function __get($key)
    {
        if ($key === 'message') {
            return $this->sent->getOriginalMessage();
        }

        throw new Exception('Unable to access undefined property on '.__CLASS__.': '.$key);
    }
}
