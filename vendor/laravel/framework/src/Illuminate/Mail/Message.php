<?php
/**
 * Illuminate，邮件，消息
 */

namespace Illuminate\Mail;

use Illuminate\Contracts\Mail\Attachable;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * @mixin \Symfony\Component\Mime\Email
 */
class Message
{
    use ForwardsCalls;

    /**
     * The Symfony Email instance.
	 * Symfony Email实例
     *
     * @var \Symfony\Component\Mime\Email
     */
    protected $message;

    /**
     * CIDs of files embedded in the message.
	 * 消息中嵌入文件的cid
     *
     * @deprecated Will be removed in a future Laravel version.
	 * 将在未来的Laravel版本中删除
     *
     * @var array
     */
    protected $embeddedFiles = [];

    /**
     * Create a new message instance.
	 * 创建一个新的消息实例
     *
     * @param  \Symfony\Component\Mime\Email  $message
     * @return void
     */
    public function __construct(Email $message)
    {
        $this->message = $message;
    }

    /**
     * Add a "from" address to the message.
	 * 在消息中添加"发件人"地址
     *
     * @param  string|array  $address
     * @param  string|null  $name
     * @return $this
     */
    public function from($address, $name = null)
    {
        is_array($address)
            ? $this->message->from(...$address)
            : $this->message->from(new Address($address, (string) $name));

        return $this;
    }

    /**
     * Set the "sender" of the message.
	 * 设置消息的"发送者"
     *
     * @param  string|array  $address
     * @param  string|null  $name
     * @return $this
     */
    public function sender($address, $name = null)
    {
        is_array($address)
            ? $this->message->sender(...$address)
            : $this->message->sender(new Address($address, (string) $name));

        return $this;
    }

    /**
     * Set the "return path" of the message.
	 * 设置消息的"返回路径"
     *
     * @param  string  $address
     * @return $this
     */
    public function returnPath($address)
    {
        $this->message->returnPath($address);

        return $this;
    }

    /**
     * Add a recipient to the message.
	 * 向邮件添加收件人
     *
     * @param  string|array  $address
     * @param  string|null  $name
     * @param  bool  $override
     * @return $this
     */
    public function to($address, $name = null, $override = false)
    {
        if ($override) {
            is_array($address)
                ? $this->message->to(...$address)
                : $this->message->to(new Address($address, (string) $name));

            return $this;
        }

        return $this->addAddresses($address, $name, 'To');
    }

    /**
     * Remove all "to" addresses from the message.
	 * 从消息中删除所有"to"地址
     *
     * @return $this
     */
    public function forgetTo()
    {
        if ($header = $this->message->getHeaders()->get('To')) {
            $this->addAddressDebugHeader('X-To', $this->message->getTo());

            $header->setAddresses([]);
        }

        return $this;
    }

    /**
     * Add a carbon copy to the message.
	 * 在邮件中添加一份复写件
     *
     * @param  string|array  $address
     * @param  string|null  $name
     * @param  bool  $override
     * @return $this
     */
    public function cc($address, $name = null, $override = false)
    {
        if ($override) {
            is_array($address)
                ? $this->message->cc(...$address)
                : $this->message->cc(new Address($address, (string) $name));

            return $this;
        }

        return $this->addAddresses($address, $name, 'Cc');
    }

    /**
     * Remove all carbon copy addresses from the message.
	 * 从邮件中删除所有的复写地址
     *
     * @return $this
     */
    public function forgetCc()
    {
        if ($header = $this->message->getHeaders()->get('Cc')) {
            $this->addAddressDebugHeader('X-Cc', $this->message->getCC());

            $header->setAddresses([]);
        }

        return $this;
    }

    /**
     * Add a blind carbon copy to the message.
	 * 在邮件中添加一份副本
     *
     * @param  string|array  $address
     * @param  string|null  $name
     * @param  bool  $override
     * @return $this
     */
    public function bcc($address, $name = null, $override = false)
    {
        if ($override) {
            is_array($address)
                ? $this->message->bcc(...$address)
                : $this->message->bcc(new Address($address, (string) $name));

            return $this;
        }

        return $this->addAddresses($address, $name, 'Bcc');
    }

    /**
     * Remove all of the blind carbon copy addresses from the message.
	 * 从邮件中删除所有的盲抄写地址
     *
     * @return $this
     */
    public function forgetBcc()
    {
        if ($header = $this->message->getHeaders()->get('Bcc')) {
            $this->addAddressDebugHeader('X-Bcc', $this->message->getBcc());

            $header->setAddresses([]);
        }

        return $this;
    }

    /**
     * Add a "reply to" address to the message.
	 * 在邮件中添加"回复"地址
     *
     * @param  string|array  $address
     * @param  string|null  $name
     * @return $this
     */
    public function replyTo($address, $name = null)
    {
        return $this->addAddresses($address, $name, 'ReplyTo');
    }

    /**
     * Add a recipient to the message.
	 * 向邮件添加收件人
     *
     * @param  string|array  $address
     * @param  string  $name
     * @param  string  $type
     * @return $this
     */
    protected function addAddresses($address, $name, $type)
    {
        if (is_array($address)) {
            $type = lcfirst($type);

            $addresses = collect($address)->map(function ($address, $key) {
                if (is_string($key) && is_string($address)) {
                    return new Address($key, $address);
                }

                if (is_array($address)) {
                    return new Address($address['email'] ?? $address['address'], $address['name'] ?? null);
                }

                if (is_null($address)) {
                    return new Address($key);
                }

                return $address;
            })->all();

            $this->message->{"{$type}"}(...$addresses);
        } else {
            $this->message->{"add{$type}"}(new Address($address, (string) $name));
        }

        return $this;
    }

    /**
     * Add an address debug header for a list of recipients.
	 * 为收件人列表添加地址调试头
     *
     * @param  string  $header
     * @param  \Symfony\Component\Mime\Address[]  $addresses
     * @return $this
     */
    protected function addAddressDebugHeader(string $header, array $addresses)
    {
        $this->message->getHeaders()->addTextHeader(
            $header,
            implode(', ', array_map(fn ($a) => $a->toString(), $addresses)),
        );

        return $this;
    }

    /**
     * Set the subject of the message.
	 * 设置邮件的主题
     *
     * @param  string  $subject
     * @return $this
     */
    public function subject($subject)
    {
        $this->message->subject($subject);

        return $this;
    }

    /**
     * Set the message priority level.
	 * 设置消息优先级
     *
     * @param  int  $level
     * @return $this
     */
    public function priority($level)
    {
        $this->message->priority($level);

        return $this;
    }

    /**
     * Attach a file to the message.
	 * 将文件附加到消息中
     *
     * @param  string|\Illuminate\Contracts\Mail\Attachable|\Illuminate\Mail\Attachment  $file
     * @param  array  $options
     * @return $this
     */
    public function attach($file, array $options = [])
    {
        if ($file instanceof Attachable) {
            $file = $file->toMailAttachment();
        }

        if ($file instanceof Attachment) {
            return $file->attachTo($this);
        }

        $this->message->attachFromPath($file, $options['as'] ?? null, $options['mime'] ?? null);

        return $this;
    }

    /**
     * Attach in-memory data as an attachment.
	 * 将内存中的数据作为附件附加
     *
     * @param  string|resource  $data
     * @param  string  $name
     * @param  array  $options
     * @return $this
     */
    public function attachData($data, $name, array $options = [])
    {
        $this->message->attach($data, $name, $options['mime'] ?? null);

        return $this;
    }

    /**
     * Embed a file in the message and get the CID.
	 * 在消息中嵌入一个文件并获取CID
     *
     * @param  string|\Illuminate\Contracts\Mail\Attachable|\Illuminate\Mail\Attachment  $file
     * @return string
     */
    public function embed($file)
    {
        if ($file instanceof Attachable) {
            $file = $file->toMailAttachment();
        }

        if ($file instanceof Attachment) {
            return $file->attachWith(
                function ($path) use ($file) {
                    $cid = $file->as ?? Str::random();

                    $this->message->embedFromPath($path, $cid, $file->mime);

                    return "cid:{$cid}";
                },
                function ($data) use ($file) {
                    $this->message->embed($data(), $file->as, $file->mime);

                    return "cid:{$file->as}";
                }
            );
        }

        $cid = Str::random(10);

        $this->message->embedFromPath($file, $cid);

        return "cid:$cid";
    }

    /**
     * Embed in-memory data in the message and get the CID.
	 * 在消息中嵌入内存数据并获得CID
     *
     * @param  string|resource  $data
     * @param  string  $name
     * @param  string|null  $contentType
     * @return string
     */
    public function embedData($data, $name, $contentType = null)
    {
        $this->message->embed($data, $name, $contentType);

        return "cid:$name";
    }

    /**
     * Get the underlying Symfony Email instance.
	 * 获取底层的Symfony Email实例
     *
     * @return \Symfony\Component\Mime\Email
     */
    public function getSymfonyMessage()
    {
        return $this->message;
    }

    /**
     * Dynamically pass missing methods to the Symfony instance.
	 * 动态地将缺少的方法传递给Symfony实例
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardDecoratedCallTo($this->message, $method, $parameters);
    }
}
