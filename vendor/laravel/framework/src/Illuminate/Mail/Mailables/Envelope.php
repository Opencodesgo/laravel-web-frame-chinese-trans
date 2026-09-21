<?php
/**
 * Illuminate，邮件，Mailables，信封
 */

namespace Illuminate\Mail\Mailables;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Conditionable;

class Envelope
{
    use Conditionable;

    /**
     * The address sending the message.
	 * 发送消息的地址
     *
     * @var \Illuminate\Mail\Mailables\Address|string|null
     */
    public $from;

    /**
     * The recipients of the message.
	 * 消息的接收者
     *
     * @var array
     */
    public $to;

    /**
     * The recipients receiving a copy of the message.
	 * 接收邮件副本的收件人
     *
     * @var array
     */
    public $cc;

    /**
     * The recipients receiving a blind copy of the message.
	 * 收件人收到消息的隐蔽的副本
     *
     * @var array
     */
    public $bcc;

    /**
     * The recipients that should be replied to.
	 * 应该回复的收件人
     *
     * @var array
     */
    public $replyTo;

    /**
     * The subject of the message.
	 * 消息的主题
     *
     * @var string|null
     */
    public $subject;

    /**
     * The message's tags.
	 * 消息的标签
     *
     * @var array
     */
    public $tags = [];

    /**
     * The message's meta data.
	 * 消息的元数据
     *
     * @var array
     */
    public $metadata = [];

    /**
     * The message's Symfony Message customization callbacks.
	 * 消息的Symfony message自定义回调
     *
     * @var array
     */
    public $using = [];

    /**
     * Create a new message envelope instance.
	 * 创建一个新的消息信封实例
     *
     * @param  \Illuminate\Mail\Mailables\Address|string|null  $from
     * @param  array  $to
     * @param  array  $cc
     * @param  array  $bcc
     * @param  array  $replyTo
     * @param  string|null  $subject
     * @param  array  $tags
     * @param  array  $metadata
     * @param  \Closure|array  $using
     * @return void
     *
     * @named-arguments-supported
     */
    public function __construct(Address|string $from = null, $to = [], $cc = [], $bcc = [], $replyTo = [], string $subject = null, array $tags = [], array $metadata = [], Closure|array $using = [])
    {
        $this->from = is_string($from) ? new Address($from) : $from;
        $this->to = $this->normalizeAddresses($to);
        $this->cc = $this->normalizeAddresses($cc);
        $this->bcc = $this->normalizeAddresses($bcc);
        $this->replyTo = $this->normalizeAddresses($replyTo);
        $this->subject = $subject;
        $this->tags = $tags;
        $this->metadata = $metadata;
        $this->using = Arr::wrap($using);
    }

    /**
     * Normalize the given array of addresses.
	 * 对给定的地址数组进行规范化
     *
     * @param  array  $addresses
     * @return array
     */
    protected function normalizeAddresses($addresses)
    {
        return collect($addresses)->map(function ($address) {
            return is_string($address) ? new Address($address) : $address;
        })->all();
    }

    /**
     * Specify who the message will be "from".
	 * 指定消息将"来自"谁
     *
     * @param  \Illuminate\Mail\Mailables\Address|string  $address
     * @param  string|null  $name
     * @return $this
     */
    public function from(Address|string $address, $name = null)
    {
        $this->from = is_string($address) ? new Address($address, $name) : $address;

        return $this;
    }

    /**
     * Add a "to" recipient to the message envelope.
	 * 在邮件信封中添加"to"收件人
     *
     * @param  \Illuminate\Mail\Mailables\Address|array|string  $address
     * @param  string|null  $name
     * @return $this
     */
    public function to(Address|array|string $address, $name = null)
    {
        $this->to = array_merge($this->to, $this->normalizeAddresses(
            is_string($name) ? [new Address($address, $name)] : Arr::wrap($address),
        ));

        return $this;
    }

    /**
     * Add a "cc" recipient to the message envelope.
	 * 在邮件信封中添加“抄送”收件人
     *
     * @param  \Illuminate\Mail\Mailables\Address|array|string  $address
     * @param  string|null  $name
     * @return $this
     */
    public function cc(Address|array|string $address, $name = null)
    {
        $this->cc = array_merge($this->cc, $this->normalizeAddresses(
            is_string($name) ? [new Address($address, $name)] : Arr::wrap($address),
        ));

        return $this;
    }

    /**
     * Add a "bcc" recipient to the message envelope.
	 * 在邮件信封中添加"密件抄送"收件人
     *
     * @param  \Illuminate\Mail\Mailables\Address|array|string  $address
     * @param  string|null  $name
     * @return $this
     */
    public function bcc(Address|array|string $address, $name = null)
    {
        $this->bcc = array_merge($this->bcc, $this->normalizeAddresses(
            is_string($name) ? [new Address($address, $name)] : Arr::wrap($address),
        ));

        return $this;
    }

    /**
     * Add a "reply to" recipient to the message envelope.
	 * 在邮件信封中添加"回复"收件人
     *
     * @param  \Illuminate\Mail\Mailables\Address|array|string  $address
     * @param  string|null  $name
     * @return $this
     */
    public function replyTo(Address|array|string $address, $name = null)
    {
        $this->replyTo = array_merge($this->replyTo, $this->normalizeAddresses(
            is_string($name) ? [new Address($address, $name)] : Arr::wrap($address),
        ));

        return $this;
    }

    /**
     * Set the subject of the message.
	 * 设置邮件的主题
     *
     * @param  string  $subject
     * @return $this
     */
    public function subject(string $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Add "tags" to the message.
	 * 给消息添加"标签"
     *
     * @param  array  $tags
     * @return $this
     */
    public function tags(array $tags)
    {
        $this->tags = array_merge($this->tags, $tags);

        return $this;
    }

    /**
     * Add a "tag" to the message.
	 * 给消息添加一个"标签"
     *
     * @param  string  $tag
     * @return $this
     */
    public function tag(string $tag)
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * Add metadata to the message.
	 * 向消息添加元数据
     *
     * @param  string  $key
     * @param  string|int  $value
     * @return $this
     */
    public function metadata(string $key, string|int $value)
    {
        $this->metadata[$key] = $value;

        return $this;
    }

    /**
     * Add a Symfony Message customization callback to the message.
	 * 向消息添加Symfony Message自定义回调
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function using(Closure $callback)
    {
        $this->using[] = $callback;

        return $this;
    }

    /**
     * Determine if the message is from the given address.
	 * 确定消息是否来自给定地址
     *
     * @param  string  $address
     * @param  string|null  $name
     * @return bool
     */
    public function isFrom(string $address, string $name = null)
    {
        if (is_null($name)) {
            return $this->from->address === $address;
        }

        return $this->from->address === $address &&
               $this->from->name === $name;
    }

    /**
     * Determine if the message has the given address as a recipient.
	 * 确定邮件是否以给定的地址作为收件人
     *
     * @param  string  $address
     * @param  string|null  $name
     * @return bool
     */
    public function hasTo(string $address, string $name = null)
    {
        return $this->hasRecipient($this->to, $address, $name);
    }

    /**
     * Determine if the message has the given address as a "cc" recipient.
	 * 确定邮件是否将给定地址作为“抄送”收件人
     *
     * @param  string  $address
     * @param  string|null  $name
     * @return bool
     */
    public function hasCc(string $address, string $name = null)
    {
        return $this->hasRecipient($this->cc, $address, $name);
    }

    /**
     * Determine if the message has the given address as a "bcc" recipient.
	 * 确定邮件是否将给定地址作为"密件抄送"收件人
     *
     * @param  string  $address
     * @param  string|null  $name
     * @return bool
     */
    public function hasBcc(string $address, string $name = null)
    {
        return $this->hasRecipient($this->bcc, $address, $name);
    }

    /**
     * Determine if the message has the given address as a "reply to" recipient.
	 * 确定消息是否将给定地址作为"答复"收件人
     *
     * @param  string  $address
     * @param  string|null  $name
     * @return bool
     */
    public function hasReplyTo(string $address, string $name = null)
    {
        return $this->hasRecipient($this->replyTo, $address, $name);
    }

    /**
     * Determine if the message has the given recipient.
	 * 确定消息是否具有给定的收件人
     *
     * @param  array  $recipients
     * @param  string  $address
     * @param  string|null  $name
     * @return bool
     */
    protected function hasRecipient(array $recipients, string $address, ?string $name = null)
    {
        return collect($recipients)->contains(function ($recipient) use ($address, $name) {
            if (is_null($name)) {
                return $recipient->address === $address;
            }

            return $recipient->address === $address &&
                   $recipient->name === $name;
        });
    }

    /**
     * Determine if the message has the given subject.
	 * 确定消息是否具有给定的主题
     *
     * @param  string  $subject
     * @return bool
     */
    public function hasSubject(string $subject)
    {
        return $this->subject === $subject;
    }

    /**
     * Determine if the message has the given metadata.
	 * 确定消息是否具有给定的元数据
     *
     * @param  string  $key
     * @param  string  $value
     * @return bool
     */
    public function hasMetadata(string $key, string $value)
    {
        return isset($this->metadata[$key]) && (string) $this->metadata[$key] === $value;
    }
}
