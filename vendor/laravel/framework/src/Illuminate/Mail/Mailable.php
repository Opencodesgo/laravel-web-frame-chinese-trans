<?php
/**
 * Illuminate，邮件，可邮寄的
 */

namespace Illuminate\Mail;

use Illuminate\Container\Container;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Mail\Attachable;
use Illuminate\Contracts\Mail\Factory as MailFactory;
use Illuminate\Contracts\Mail\Mailable as MailableContract;
use Illuminate\Contracts\Queue\Factory as Queue;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Localizable;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Testing\Constraints\SeeInOrder;
use PHPUnit\Framework\Assert as PHPUnit;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mailer\Header\TagHeader;
use Symfony\Component\Mime\Address;

class Mailable implements MailableContract, Renderable
{
    use Conditionable, ForwardsCalls, Localizable, Macroable {
        __call as macroCall;
    }

    /**
     * The locale of the message.
	 * 消息的区域设置
     *
     * @var string
     */
    public $locale;

    /**
     * The person the message is from.
	 * 信息来自的人
     *
     * @var array
     */
    public $from = [];

    /**
     * The "to" recipients of the message.
	 * 消息的"to"收件人
     *
     * @var array
     */
    public $to = [];

    /**
     * The "cc" recipients of the message.
	 * 邮件的"抄送"收件人
     *
     * @var array
     */
    public $cc = [];

    /**
     * The "bcc" recipients of the message.
	 * 消息的"密件抄送"收件人
     *
     * @var array
     */
    public $bcc = [];

    /**
     * The "reply to" recipients of the message.
	 * 消息的"回复"收件人
     *
     * @var array
     */
    public $replyTo = [];

    /**
     * The subject of the message.
	 * 消息的主题
     *
     * @var string
     */
    public $subject;

    /**
     * The Markdown template for the message (if applicable).
	 * 消息的Markdown模板（如果适用）
     *
     * @var string
     */
    public $markdown;

    /**
     * The HTML to use for the message.
	 * 用于消息的HTML
     *
     * @var string
     */
    protected $html;

    /**
     * The view to use for the message.
	 * 要用于消息的视图
     *
     * @var string
     */
    public $view;

    /**
     * The plain text view to use for the message.
	 * 要用于消息的纯文本视图
     *
     * @var string
     */
    public $textView;

    /**
     * The view data for the message.
	 * 消息的视图数据
     *
     * @var array
     */
    public $viewData = [];

    /**
     * The attachments for the message.
	 * 消息的附件
     *
     * @var array
     */
    public $attachments = [];

    /**
     * The raw attachments for the message.
	 * 消息的原始附件
     *
     * @var array
     */
    public $rawAttachments = [];

    /**
     * The attachments from a storage disk.
	 * 来自存储磁盘的附件
     *
     * @var array
     */
    public $diskAttachments = [];

    /**
     * The tags for the message.
	 * 消息的标签
     *
     * @var array
     */
    protected $tags = [];

    /**
     * The metadata for the message.
	 * 消息的元数据
     *
     * @var array
     */
    protected $metadata = [];

    /**
     * The callbacks for the message.
	 * 消息的回调
     *
     * @var array
     */
    public $callbacks = [];

    /**
     * The name of the theme that should be used when formatting the message.
	 * 在格式化消息时应使用的主题名称
     *
     * @var string|null
     */
    public $theme;

    /**
     * The name of the mailer that should send the message.
	 * 应该发送邮件的邮件者的名称
     *
     * @var string
     */
    public $mailer;

    /**
     * The rendered mailable views for testing / assertions.
	 * 为测试/断言呈现的可邮寄视图
     *
     * @var array
     */
    protected $assertionableRenderStrings;

    /**
     * The callback that should be invoked while building the view data.
	 * 在构建视图数据时应该调用的回调
     *
     * @var callable
     */
    public static $viewDataCallback;

    /**
     * Send the message using the given mailer.
	 * 使用给定的邮件发送器发送消息
     *
     * @param  \Illuminate\Contracts\Mail\Factory|\Illuminate\Contracts\Mail\Mailer  $mailer
     * @return \Illuminate\Mail\SentMessage|null
     */
    public function send($mailer)
    {
        return $this->withLocale($this->locale, function () use ($mailer) {
            $this->prepareMailableForDelivery();

            $mailer = $mailer instanceof MailFactory
                            ? $mailer->mailer($this->mailer)
                            : $mailer;

            return $mailer->send($this->buildView(), $this->buildViewData(), function ($message) {
                $this->buildFrom($message)
                     ->buildRecipients($message)
                     ->buildSubject($message)
                     ->buildTags($message)
                     ->buildMetadata($message)
                     ->runCallbacks($message)
                     ->buildAttachments($message);
            });
        });
    }

    /**
     * Queue the message for sending.
	 * 将消息排队等待发送
     *
     * @param  \Illuminate\Contracts\Queue\Factory  $queue
     * @return mixed
     */
    public function queue(Queue $queue)
    {
        if (isset($this->delay)) {
            return $this->later($this->delay, $queue);
        }

        $connection = property_exists($this, 'connection') ? $this->connection : null;

        $queueName = property_exists($this, 'queue') ? $this->queue : null;

        return $queue->connection($connection)->pushOn(
            $queueName ?: null, $this->newQueuedJob()
        );
    }

    /**
     * Deliver the queued message after (n) seconds.
	 * 在(n)秒后交付排队消息
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @param  \Illuminate\Contracts\Queue\Factory  $queue
     * @return mixed
     */
    public function later($delay, Queue $queue)
    {
        $connection = property_exists($this, 'connection') ? $this->connection : null;

        $queueName = property_exists($this, 'queue') ? $this->queue : null;

        return $queue->connection($connection)->laterOn(
            $queueName ?: null, $delay, $this->newQueuedJob()
        );
    }

    /**
     * Make the queued mailable job instance.
	 * 创建排队可投递作业实例
     *
     * @return mixed
     */
    protected function newQueuedJob()
    {
        return Container::getInstance()->make(SendQueuedMailable::class, ['mailable' => $this])
                    ->through(array_merge(
                        method_exists($this, 'middleware') ? $this->middleware() : [],
                        $this->middleware ?? []
                    ));
    }

    /**
     * Render the mailable into a view.
	 * 将邮件呈现到视图中
     *
     * @return string
     *
     * @throws \ReflectionException
     */
    public function render()
    {
        return $this->withLocale($this->locale, function () {
            $this->prepareMailableForDelivery();

            return Container::getInstance()->make('mailer')->render(
                $this->buildView(), $this->buildViewData()
            );
        });
    }

    /**
     * Build the view for the message.
	 * 为消息构建视图
     *
     * @return array|string
     *
     * @throws \ReflectionException
     */
    protected function buildView()
    {
        if (isset($this->html)) {
            return array_filter([
                'html' => new HtmlString($this->html),
                'text' => $this->textView ?? null,
            ]);
        }

        if (isset($this->markdown)) {
            return $this->buildMarkdownView();
        }

        if (isset($this->view, $this->textView)) {
            return [$this->view, $this->textView];
        } elseif (isset($this->textView)) {
            return ['text' => $this->textView];
        }

        return $this->view;
    }

    /**
     * Build the Markdown view for the message.
	 * 为消息构建Markdown视图
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    protected function buildMarkdownView()
    {
        $markdown = Container::getInstance()->make(Markdown::class);

        if (isset($this->theme)) {
            $markdown->theme($this->theme);
        }

        $data = $this->buildViewData();

        return [
            'html' => $markdown->render($this->markdown, $data),
            'text' => $this->buildMarkdownText($markdown, $data),
        ];
    }

    /**
     * Build the view data for the message.
	 * 为消息构建视图数据
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    public function buildViewData()
    {
        $data = $this->viewData;

        if (static::$viewDataCallback) {
            $data = array_merge($data, call_user_func(static::$viewDataCallback, $this));
        }

        foreach ((new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->getDeclaringClass()->getName() !== self::class) {
                $data[$property->getName()] = $property->getValue($this);
            }
        }

        return $data;
    }

    /**
     * Build the text view for a Markdown message.
	 * 为Markdown消息构建文本视图
     *
     * @param  \Illuminate\Mail\Markdown  $markdown
     * @param  array  $data
     * @return string
     */
    protected function buildMarkdownText($markdown, $data)
    {
        return $this->textView
                ?? $markdown->renderText($this->markdown, $data);
    }

    /**
     * Add the sender to the message.
	 * 将发送者添加到消息中
     *
     * @param  \Illuminate\Mail\Message  $message
     * @return $this
     */
    protected function buildFrom($message)
    {
        if (! empty($this->from)) {
            $message->from($this->from[0]['address'], $this->from[0]['name']);
        }

        return $this;
    }

    /**
     * Add all of the recipients to the message.
	 * 将所有收件人添加到邮件中
     *
     * @param  \Illuminate\Mail\Message  $message
     * @return $this
     */
    protected function buildRecipients($message)
    {
        foreach (['to', 'cc', 'bcc', 'replyTo'] as $type) {
            foreach ($this->{$type} as $recipient) {
                $message->{$type}($recipient['address'], $recipient['name']);
            }
        }

        return $this;
    }

    /**
     * Set the subject for the message.
	 * 为邮件设置主题
     *
     * @param  \Illuminate\Mail\Message  $message
     * @return $this
     */
    protected function buildSubject($message)
    {
        if ($this->subject) {
            $message->subject($this->subject);
        } else {
            $message->subject(Str::title(Str::snake(class_basename($this), ' ')));
        }

        return $this;
    }

    /**
     * Add all of the attachments to the message.
	 * 将所有附件添加到消息中
     *
     * @param  \Illuminate\Mail\Message  $message
     * @return $this
     */
    protected function buildAttachments($message)
    {
        foreach ($this->attachments as $attachment) {
            $message->attach($attachment['file'], $attachment['options']);
        }

        foreach ($this->rawAttachments as $attachment) {
            $message->attachData(
                $attachment['data'], $attachment['name'], $attachment['options']
            );
        }

        $this->buildDiskAttachments($message);

        return $this;
    }

    /**
     * Add all of the disk attachments to the message.
	 * 将所有磁盘附件添加到消息中
     *
     * @param  \Illuminate\Mail\Message  $message
     * @return void
     */
    protected function buildDiskAttachments($message)
    {
        foreach ($this->diskAttachments as $attachment) {
            $storage = Container::getInstance()->make(
                FilesystemFactory::class
            )->disk($attachment['disk']);

            $message->attachData(
                $storage->get($attachment['path']),
                $attachment['name'] ?? basename($attachment['path']),
                array_merge(['mime' => $storage->mimeType($attachment['path'])], $attachment['options'])
            );
        }
    }

    /**
     * Add all defined tags to the message.
	 * 将所有定义的标记添加到消息中
     *
     * @param  \Illuminate\Mail\Message  $message
     * @return $this
     */
    protected function buildTags($message)
    {
        if ($this->tags) {
            foreach ($this->tags as $tag) {
                $message->getHeaders()->add(new TagHeader($tag));
            }
        }

        return $this;
    }

    /**
     * Add all defined metadata to the message.
	 * 将所有定义的元数据添加到消息中
     *
     * @param  \Illuminate\Mail\Message  $message
     * @return $this
     */
    protected function buildMetadata($message)
    {
        if ($this->metadata) {
            foreach ($this->metadata as $key => $value) {
                $message->getHeaders()->add(new MetadataHeader($key, $value));
            }
        }

        return $this;
    }

    /**
     * Run the callbacks for the message.
	 * 运行消息的回调
     *
     * @param  \Illuminate\Mail\Message  $message
     * @return $this
     */
    protected function runCallbacks($message)
    {
        foreach ($this->callbacks as $callback) {
            $callback($message->getSymfonyMessage());
        }

        return $this;
    }

    /**
     * Set the locale of the message.
	 * 设置消息的区域设置
     *
     * @param  string  $locale
     * @return $this
     */
    public function locale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Set the priority of this message.
	 * 设置此消息的优先级
     *
     * The value is an integer where 1 is the highest priority and 5 is the lowest.
	 * 整数形式，优先级为1最高，优先级为5最低。
     *
     * @param  int  $level
     * @return $this
     */
    public function priority($level = 3)
    {
        $this->callbacks[] = function ($message) use ($level) {
            $message->priority($level);
        };

        return $this;
    }

    /**
     * Set the sender of the message.
	 * 设置消息的发送者
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @return $this
     */
    public function from($address, $name = null)
    {
        return $this->setAddress($address, $name, 'from');
    }

    /**
     * Determine if the given recipient is set on the mailable.
	 * 确定是否在邮件上设置了给定的收件人
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @return bool
     */
    public function hasFrom($address, $name = null)
    {
        return $this->hasRecipient($address, $name, 'from');
    }

    /**
     * Set the recipients of the message.
	 * 设置邮件的收件人
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @return $this
     */
    public function to($address, $name = null)
    {
        if (! $this->locale && $address instanceof HasLocalePreference) {
            $this->locale($address->preferredLocale());
        }

        return $this->setAddress($address, $name, 'to');
    }

    /**
     * Determine if the given recipient is set on the mailable.
	 * 确定是否在邮件上设置了给定的收件人
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @return bool
     */
    public function hasTo($address, $name = null)
    {
        return $this->hasRecipient($address, $name, 'to');
    }

    /**
     * Set the recipients of the message.
	 * 设置邮件的收件人
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @return $this
     */
    public function cc($address, $name = null)
    {
        return $this->setAddress($address, $name, 'cc');
    }

    /**
     * Determine if the given recipient is set on the mailable.
	 * 确定是否在邮件上设置了给定的收件人
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @return bool
     */
    public function hasCc($address, $name = null)
    {
        return $this->hasRecipient($address, $name, 'cc');
    }

    /**
     * Set the recipients of the message.
	 * 设置邮件的收件人
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @return $this
     */
    public function bcc($address, $name = null)
    {
        return $this->setAddress($address, $name, 'bcc');
    }

    /**
     * Determine if the given recipient is set on the mailable.
	 * 确定是否在邮件上设置了给定的收件人
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @return bool
     */
    public function hasBcc($address, $name = null)
    {
        return $this->hasRecipient($address, $name, 'bcc');
    }

    /**
     * Set the "reply to" address of the message.
	 * 设置邮件的"回复"地址
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @return $this
     */
    public function replyTo($address, $name = null)
    {
        return $this->setAddress($address, $name, 'replyTo');
    }

    /**
     * Determine if the given replyTo is set on the mailable.
	 * 确定是否在邮件上设置了给定的replyTo
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @return bool
     */
    public function hasReplyTo($address, $name = null)
    {
        return $this->hasRecipient($address, $name, 'replyTo');
    }

    /**
     * Set the recipients of the message.
	 * 设置邮件的收件人
     *
     * All recipients are stored internally as [['name' => ?, 'address' => ?]]
	 * 所有收件人在内部存储为[['name' => ?]， 'address' => ？]]
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @param  string  $property
     * @return $this
     */
    protected function setAddress($address, $name = null, $property = 'to')
    {
        if (empty($address)) {
            return $this;
        }

        foreach ($this->addressesToArray($address, $name) as $recipient) {
            $recipient = $this->normalizeRecipient($recipient);

            $this->{$property}[] = [
                'name' => $recipient->name ?? null,
                'address' => $recipient->email,
            ];
        }

        $this->{$property} = collect($this->{$property})
            ->reverse()
            ->unique('address')
            ->reverse()
            ->values()
            ->all();

        return $this;
    }

    /**
     * Convert the given recipient arguments to an array.
	 * 将给定的接收方参数转换为数组
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @return array
     */
    protected function addressesToArray($address, $name)
    {
        if (! is_array($address) && ! $address instanceof Collection) {
            $address = is_string($name) ? [['name' => $name, 'email' => $address]] : [$address];
        }

        return $address;
    }

    /**
     * Convert the given recipient into an object.
	 * 将给定的接收者转换为对象
     *
     * @param  mixed  $recipient
     * @return object
     */
    protected function normalizeRecipient($recipient)
    {
        if (is_array($recipient)) {
            if (array_values($recipient) === $recipient) {
                return (object) array_map(function ($email) {
                    return compact('email');
                }, $recipient);
            }

            return (object) $recipient;
        } elseif (is_string($recipient)) {
            return (object) ['email' => $recipient];
        } elseif ($recipient instanceof Address) {
            return (object) ['email' => $recipient->getAddress(), 'name' => $recipient->getName()];
        } elseif ($recipient instanceof Mailables\Address) {
            return (object) ['email' => $recipient->address, 'name' => $recipient->name];
        }

        return $recipient;
    }

    /**
     * Determine if the given recipient is set on the mailable.
	 * 确定是否在邮件上设置了给定的收件人
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @param  string  $property
     * @return bool
     */
    protected function hasRecipient($address, $name = null, $property = 'to')
    {
        if (empty($address)) {
            return false;
        }

        $expected = $this->normalizeRecipient(
            $this->addressesToArray($address, $name)[0]
        );

        $expected = [
            'name' => $expected->name ?? null,
            'address' => $expected->email,
        ];

        if ($this->hasEnvelopeRecipient($expected['address'], $expected['name'], $property)) {
            return true;
        }

        return collect($this->{$property})->contains(function ($actual) use ($expected) {
            if (! isset($expected['name'])) {
                return $actual['address'] == $expected['address'];
            }

            return $actual == $expected;
        });
    }

    /**
     * Determine if the mailable "envelope" method defines a recipient.
	 * 确定可邮寄的“信封”方法是否定义了收件人
     *
     * @param  string  $address
     * @param  string|null  $name
     * @param  string  $property
     * @return bool
     */
    private function hasEnvelopeRecipient($address, $name, $property)
    {
        return method_exists($this, 'envelope') && match ($property) {
            'from' => $this->envelope()->isFrom($address, $name),
            'to' => $this->envelope()->hasTo($address, $name),
            'cc' => $this->envelope()->hasCc($address, $name),
            'bcc' => $this->envelope()->hasBcc($address, $name),
            'replyTo' => $this->envelope()->hasReplyTo($address, $name),
        };
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
        $this->subject = $subject;

        return $this;
    }

    /**
     * Determine if the mailable has the given subject.
	 * 确定邮件是否具有给定的主题
     *
     * @param  string  $subject
     * @return bool
     */
    public function hasSubject($subject)
    {
        return $this->subject === $subject ||
               (method_exists($this, 'envelope') && $this->envelope()->hasSubject($subject));
    }

    /**
     * Set the Markdown template for the message.
	 * 为邮件设置Markdown模板
     *
     * @param  string  $view
     * @param  array  $data
     * @return $this
     */
    public function markdown($view, array $data = [])
    {
        $this->markdown = $view;
        $this->viewData = array_merge($this->viewData, $data);

        return $this;
    }

    /**
     * Set the view and view data for the message.
	 * 设置消息的视图和视图数据
     *
     * @param  string  $view
     * @param  array  $data
     * @return $this
     */
    public function view($view, array $data = [])
    {
        $this->view = $view;
        $this->viewData = array_merge($this->viewData, $data);

        return $this;
    }

    /**
     * Set the rendered HTML content for the message.
	 * 为消息设置呈现的HTML内容
     *
     * @param  string  $html
     * @return $this
     */
    public function html($html)
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Set the plain text view for the message.
	 * 设置消息的纯文本视图
     *
     * @param  string  $textView
     * @param  array  $data
     * @return $this
     */
    public function text($textView, array $data = [])
    {
        $this->textView = $textView;
        $this->viewData = array_merge($this->viewData, $data);

        return $this;
    }

    /**
     * Set the view data for the message.
	 * 设置消息的视图数据
     *
     * @param  string|array  $key
     * @param  mixed  $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->viewData = array_merge($this->viewData, $key);
        } else {
            $this->viewData[$key] = $value;
        }

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

        $this->attachments = collect($this->attachments)
                    ->push(compact('file', 'options'))
                    ->unique('file')
                    ->all();

        return $this;
    }

    /**
     * Attach multiple files to the message.
	 * 将多个文件附加到消息中
     *
     * @param  array  $files
     * @return $this
     */
    public function attachMany($files)
    {
        foreach ($files as $file => $options) {
            if (is_int($file)) {
                $this->attach($options);
            } else {
                $this->attach($file, $options);
            }
        }

        return $this;
    }

    /**
     * Determine if the mailable has the given attachment.
	 * 确定邮件是否具有给定的附件
     *
     * @param  string|\Illuminate\Contracts\Mail\Attachable|\Illuminate\Mail\Attachment  $file
     * @param  array  $options
     * @return bool
     */
    public function hasAttachment($file, array $options = [])
    {
        if ($file instanceof Attachable) {
            $file = $file->toMailAttachment();
        }

        if ($file instanceof Attachment && $this->hasEnvelopeAttachment($file)) {
            return true;
        }

        if ($file instanceof Attachment) {
            $parts = $file->attachWith(
                fn ($path) => [$path, ['as' => $file->as, 'mime' => $file->mime]],
                fn ($data) => $this->hasAttachedData($data(), $file->as, ['mime' => $file->mime])
            );

            if ($parts === true) {
                return true;
            }

            [$file, $options] = $parts === false
                ? [null, []]
                : $parts;
        }

        return collect($this->attachments)->contains(
            fn ($attachment) => $attachment['file'] === $file && array_filter($attachment['options']) === array_filter($options)
        );
    }

    /**
     * Determine if the mailable has the given envelope attachment.
	 * 确定邮件是否具有给定的信封附件
     *
     * @param  \Illuminate\Mail\Attachment  $attachment
     * @return bool
     */
    private function hasEnvelopeAttachment($attachment)
    {
        if (! method_exists($this, 'envelope')) {
            return false;
        }

        $attachments = $this->attachments();

        return Collection::make(is_object($attachments) ? [$attachments] : $attachments)
                ->map(fn ($attached) => $attached instanceof Attachable ? $attached->toMailAttachment() : $attached)
                ->contains(fn ($attached) => $attached->isEquivalent($attachment));
    }

    /**
     * Attach a file to the message from storage.
	 * 将文件从存储器附加到消息上
     *
     * @param  string  $path
     * @param  string|null  $name
     * @param  array  $options
     * @return $this
     */
    public function attachFromStorage($path, $name = null, array $options = [])
    {
        return $this->attachFromStorageDisk(null, $path, $name, $options);
    }

    /**
     * Attach a file to the message from storage.
	 * 将文件从存储器附加到消息上
     *
     * @param  string  $disk
     * @param  string  $path
     * @param  string|null  $name
     * @param  array  $options
     * @return $this
     */
    public function attachFromStorageDisk($disk, $path, $name = null, array $options = [])
    {
        $this->diskAttachments = collect($this->diskAttachments)->push([
            'disk' => $disk,
            'path' => $path,
            'name' => $name ?? basename($path),
            'options' => $options,
        ])->unique(function ($file) {
            return $file['name'].$file['disk'].$file['path'];
        })->all();

        return $this;
    }

    /**
     * Determine if the mailable has the given attachment from storage.
	 * 确定邮件是否具有来自存储的给定附件
     *
     * @param  string  $path
     * @param  string|null  $name
     * @param  array  $options
     * @return bool
     */
    public function hasAttachmentFromStorage($path, $name = null, array $options = [])
    {
        return $this->hasAttachmentFromStorageDisk(null, $path, $name, $options);
    }

    /**
     * Determine if the mailable has the given attachment from a specific storage disk.
	 * 确定邮件是否具有来自特定存储磁盘的给定附件
     *
     * @param  string  $disk
     * @param  string  $path
     * @param  string|null  $name
     * @param  array  $options
     * @return bool
     */
    public function hasAttachmentFromStorageDisk($disk, $path, $name = null, array $options = [])
    {
        return collect($this->diskAttachments)->contains(
            fn ($attachment) => $attachment['disk'] === $disk
                && $attachment['path'] === $path
                && $attachment['name'] === ($name ?? basename($path))
                && $attachment['options'] === $options
        );
    }

    /**
     * Attach in-memory data as an attachment.
	 * 将内存中的数据作为附件附加
     *
     * @param  string  $data
     * @param  string  $name
     * @param  array  $options
     * @return $this
     */
    public function attachData($data, $name, array $options = [])
    {
        $this->rawAttachments = collect($this->rawAttachments)
                ->push(compact('data', 'name', 'options'))
                ->unique(function ($file) {
                    return $file['name'].$file['data'];
                })->all();

        return $this;
    }

    /**
     * Determine if the mailable has the given data as an attachment.
	 * 确定邮件是否将给定的数据作为附件
     *
     * @param  string  $data
     * @param  string  $name
     * @param  array  $options
     * @return bool
     */
    public function hasAttachedData($data, $name, array $options = [])
    {
        return collect($this->rawAttachments)->contains(
            fn ($attachment) => $attachment['data'] === $data
                && $attachment['name'] === $name
                && array_filter($attachment['options']) === array_filter($options)
        );
    }

    /**
     * Add a tag header to the message when supported by the underlying transport.
	 * 在底层传输支持的情况下，向消息添加标记标头。
     *
     * @param  string  $value
     * @return $this
     */
    public function tag($value)
    {
        array_push($this->tags, $value);

        return $this;
    }

    /**
     * Determine if the mailable has the given tag.
	 * 确定mailable是否具有给定的标记
     *
     * @param  string  $value
     * @return bool
     */
    public function hasTag($value)
    {
        return in_array($value, $this->tags) ||
               (method_exists($this, 'envelope') && in_array($value, $this->envelope()->tags));
    }

    /**
     * Add a metadata header to the message when supported by the underlying transport.
	 * 当底层传输支持时，向消息添加元数据标头。
     *
     * @param  string  $key
     * @param  string  $value
     * @return $this
     */
    public function metadata($key, $value)
    {
        $this->metadata[$key] = $value;

        return $this;
    }

    /**
     * Determine if the mailable has the given metadata.
	 * 确定邮件是否具有给定的元数据
     *
     * @param  string  $key
     * @param  string  $value
     * @return bool
     */
    public function hasMetadata($key, $value)
    {
        return (isset($this->metadata[$key]) && $this->metadata[$key] === $value) ||
               (method_exists($this, 'envelope') && $this->envelope()->hasMetadata($key, $value));
    }

    /**
     * Assert that the mailable is from the given address.
	 * 断言邮件来自给定地址
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @return $this
     */
    public function assertFrom($address, $name = null)
    {
        $recipient = $this->formatAssertionRecipient($address, $name);

        PHPUnit::assertTrue(
            $this->hasFrom($address, $name),
            "Email was not from expected address [{$recipient}]."
        );

        return $this;
    }

    /**
     * Assert that the mailable has the given recipient.
	 * 断言邮件具有给定的收件人
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @return $this
     */
    public function assertTo($address, $name = null)
    {
        $recipient = $this->formatAssertionRecipient($address, $name);

        PHPUnit::assertTrue(
            $this->hasTo($address, $name),
            "Did not see expected recipient [{$recipient}] in email recipients."
        );

        return $this;
    }

    /**
     * Assert that the mailable has the given recipient.
	 * 断言邮件具有给定的收件人
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @return $this
     */
    public function assertHasTo($address, $name = null)
    {
        return $this->assertTo($address, $name);
    }

    /**
     * Assert that the mailable has the given recipient.
	 * 断言邮件具有给定的收件人
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @return $this
     */
    public function assertHasCc($address, $name = null)
    {
        $recipient = $this->formatAssertionRecipient($address, $name);

        PHPUnit::assertTrue(
            $this->hasCc($address, $name),
            "Did not see expected recipient [{$recipient}] in email recipients."
        );

        return $this;
    }

    /**
     * Assert that the mailable has the given recipient.
	 * 断言邮件具有给定的收件人
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @return $this
     */
    public function assertHasBcc($address, $name = null)
    {
        $recipient = $this->formatAssertionRecipient($address, $name);

        PHPUnit::assertTrue(
            $this->hasBcc($address, $name),
            "Did not see expected recipient [{$recipient}] in email recipients."
        );

        return $this;
    }

    /**
     * Assert that the mailable has the given "reply to" address.
	 * 断言邮件具有给定的"回复"地址
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @return $this
     */
    public function assertHasReplyTo($address, $name = null)
    {
        $replyTo = $this->formatAssertionRecipient($address, $name);

        PHPUnit::assertTrue(
            $this->hasReplyTo($address, $name),
            "Did not see expected address [{$replyTo}] as email 'reply to' recipient."
        );

        return $this;
    }

    /**
     * Format the mailable recipient for display in an assertion message.
	 * 格式化可发送的收件人，以便在断言消息中显示。
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @return string
     */
    private function formatAssertionRecipient($address, $name = null)
    {
        if (! is_string($address)) {
            $address = json_encode($address);
        }

        if (filled($name)) {
            $address .= ' ('.$name.')';
        }

        return $address;
    }

    /**
     * Assert that the mailable has the given subject.
	 * 断言邮件具有给定的主题
     *
     * @param  string  $subject
     * @return $this
     */
    public function assertHasSubject($subject)
    {
        PHPUnit::assertTrue(
            $this->hasSubject($subject),
            "Did not see expected text [{$subject}] in email subject."
        );

        return $this;
    }

    /**
     * Assert that the given text is present in the HTML email body.
	 * 断言给定的文本存在于HTML电子邮件正文中
     *
     * @param  string  $string
     * @return $this
     */
    public function assertSeeInHtml($string)
    {
        [$html, $text] = $this->renderForAssertions();

        PHPUnit::assertStringContainsString(
            $string,
            $html,
            "Did not see expected text [{$string}] within email body."
        );

        return $this;
    }

    /**
     * Assert that the given text is not present in the HTML email body.
	 * 断言给定的文本不存在于HTML邮件正文中
     *
     * @param  string  $string
     * @return $this
     */
    public function assertDontSeeInHtml($string)
    {
        [$html, $text] = $this->renderForAssertions();

        PHPUnit::assertStringNotContainsString(
            $string,
            $html,
            "Saw unexpected text [{$string}] within email body."
        );

        return $this;
    }

    /**
     * Assert that the given text strings are present in order in the HTML email body.
	 * 断言给定的文本字符串按顺序出现在HTML电子邮件正文中
     *
     * @param  array  $strings
     * @return $this
     */
    public function assertSeeInOrderInHtml($strings)
    {
        [$html, $text] = $this->renderForAssertions();

        PHPUnit::assertThat($strings, new SeeInOrder($html));

        return $this;
    }

    /**
     * Assert that the given text is present in the plain-text email body.
	 * 断言给定的文本存在于纯文本电子邮件正文中
     *
     * @param  string  $string
     * @return $this
     */
    public function assertSeeInText($string)
    {
        [$html, $text] = $this->renderForAssertions();

        PHPUnit::assertStringContainsString(
            $string,
            $text,
            "Did not see expected text [{$string}] within text email body."
        );

        return $this;
    }

    /**
     * Assert that the given text is not present in the plain-text email body.
	 * 断言给定的文本不存在于纯文本电子邮件正文中
     *
     * @param  string  $string
     * @return $this
     */
    public function assertDontSeeInText($string)
    {
        [$html, $text] = $this->renderForAssertions();

        PHPUnit::assertStringNotContainsString(
            $string,
            $text,
            "Saw unexpected text [{$string}] within text email body."
        );

        return $this;
    }

    /**
     * Assert that the given text strings are present in order in the plain-text email body.
	 * 断言给定的文本字符串按顺序出现在纯文本电子邮件正文中
     *
     * @param  array  $strings
     * @return $this
     */
    public function assertSeeInOrderInText($strings)
    {
        [$html, $text] = $this->renderForAssertions();

        PHPUnit::assertThat($strings, new SeeInOrder($text));

        return $this;
    }

    /**
     * Assert the mailable has the given attachment.
	 * 断言邮件具有给定的附件
     *
     * @param  string|\Illuminate\Contracts\Mail\Attachable|\Illuminate\Mail\Attachment  $file
     * @param  array  $options
     * @return $this
     */
    public function assertHasAttachment($file, array $options = [])
    {
        $this->renderForAssertions();

        PHPUnit::assertTrue(
            $this->hasAttachment($file, $options),
            'Did not find the expected attachment.'
        );

        return $this;
    }

    /**
     * Assert the mailable has the given data as an attachment.
	 * 断言邮件具有作为附件的给定数据
     *
     * @param  string  $data
     * @param  string  $name
     * @param  array  $options
     * @return $this
     */
    public function assertHasAttachedData($data, $name, array $options = [])
    {
        $this->renderForAssertions();

        PHPUnit::assertTrue(
            $this->hasAttachedData($data, $name, $options),
            'Did not find the expected attachment.'
        );

        return $this;
    }

    /**
     * Assert the mailable has the given attachment from storage.
	 * 断言邮件具有来自存储的给定附件
     *
     * @param  string  $path
     * @param  string|null  $name
     * @param  array  $options
     * @return $this
     */
    public function assertHasAttachmentFromStorage($path, $name = null, array $options = [])
    {
        $this->renderForAssertions();

        PHPUnit::assertTrue(
            $this->hasAttachmentFromStorage($path, $name, $options),
            'Did not find the expected attachment.'
        );

        return $this;
    }

    /**
     * Assert the mailable has the given attachment from a specific storage disk.
	 * 断言邮件具有来自特定存储磁盘的给定附件
     *
     * @param  string  $disk
     * @param  string  $path
     * @param  string|null  $name
     * @param  array  $options
     * @return $this
     */
    public function assertHasAttachmentFromStorageDisk($disk, $path, $name = null, array $options = [])
    {
        $this->renderForAssertions();

        PHPUnit::assertTrue(
            $this->hasAttachmentFromStorageDisk($disk, $path, $name, $options),
            'Did not find the expected attachment.'
        );

        return $this;
    }

    /**
     * Assert that the mailable has the given tag.
	 * 断言邮件具有给定的标记
     *
     * @param  string  $tag
     * @return $this
     */
    public function assertHasTag($tag)
    {
        PHPUnit::assertTrue(
            $this->hasTag($tag),
            "Did not see expected tag [{$tag}] in email tags."
        );

        return $this;
    }

    /**
     * Assert that the mailable has the given metadata.
	 * 断言邮件具有给定的元数据
     *
     * @param  string  $key
     * @param  string  $value
     * @return $this
     */
    public function assertHasMetadata($key, $value)
    {
        PHPUnit::assertTrue(
            $this->hasMetadata($key, $value),
            "Did not see expected key [{$key}] and value [{$value}] in email metadata."
        );

        return $this;
    }

    /**
     * Render the HTML and plain-text version of the mailable into views for assertions.
	 * 将可邮件的HTML和纯文本版本呈现到断言视图中
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    protected function renderForAssertions()
    {
        if ($this->assertionableRenderStrings) {
            return $this->assertionableRenderStrings;
        }

        return $this->assertionableRenderStrings = $this->withLocale($this->locale, function () {
            $this->prepareMailableForDelivery();

            $html = Container::getInstance()->make('mailer')->render(
                $view = $this->buildView(), $this->buildViewData()
            );

            if (is_array($view) && isset($view[1])) {
                $text = $view[1];
            }

            $text ??= $view['text'] ?? '';

            if (! empty($text) && ! $text instanceof Htmlable) {
                $text = Container::getInstance()->make('mailer')->render(
                    $text, $this->buildViewData()
                );
            }

            return [(string) $html, (string) $text];
        });
    }

    /**
     * Prepare the mailable instance for delivery.
	 * 为交付准备可邮寄实例
     *
     * @return void
     */
    private function prepareMailableForDelivery()
    {
        if (method_exists($this, 'build')) {
            Container::getInstance()->call([$this, 'build']);
        }

        $this->ensureHeadersAreHydrated();
        $this->ensureEnvelopeIsHydrated();
        $this->ensureContentIsHydrated();
        $this->ensureAttachmentsAreHydrated();
    }

    /**
     * Ensure the mailable's headers are hydrated from the "headers" method.
	 * 确保mailable的标头已从"headers"方法中获取
     *
     * @return void
     */
    private function ensureHeadersAreHydrated()
    {
        if (! method_exists($this, 'headers')) {
            return;
        }

        $headers = $this->headers();

        $this->withSymfonyMessage(function ($message) use ($headers) {
            if ($headers->messageId) {
                $message->getHeaders()->addIdHeader('Message-Id', $headers->messageId);
            }

            if (count($headers->references) > 0) {
                $message->getHeaders()->addTextHeader('References', $headers->referencesString());
            }

            foreach ($headers->text as $key => $value) {
                $message->getHeaders()->addTextHeader($key, $value);
            }
        });
    }

    /**
     * Ensure the mailable's "envelope" data is hydrated from the "envelope" method.
	 * 确保mailable的“信封”数据是从"信封"方法中获取的
     *
     * @return void
     */
    private function ensureEnvelopeIsHydrated()
    {
        if (! method_exists($this, 'envelope')) {
            return;
        }

        $envelope = $this->envelope();

        if (isset($envelope->from)) {
            $this->from($envelope->from->address, $envelope->from->name);
        }

        foreach (['to', 'cc', 'bcc', 'replyTo'] as $type) {
            foreach ($envelope->{$type} as $address) {
                $this->{$type}($address->address, $address->name);
            }
        }

        if ($envelope->subject) {
            $this->subject($envelope->subject);
        }

        foreach ($envelope->tags as $tag) {
            $this->tag($tag);
        }

        foreach ($envelope->metadata as $key => $value) {
            $this->metadata($key, $value);
        }

        foreach ($envelope->using as $callback) {
            $this->withSymfonyMessage($callback);
        }
    }

    /**
     * Ensure the mailable's content is hydrated from the "content" method.
	 * 确保mailable的内容从"content"方法中得到水分
     *
     * @return void
     */
    private function ensureContentIsHydrated()
    {
        if (! method_exists($this, 'content')) {
            return;
        }

        $content = $this->content();

        if ($content->view) {
            $this->view($content->view);
        }

        if ($content->html) {
            $this->view($content->html);
        }

        if ($content->text) {
            $this->text($content->text);
        }

        if ($content->markdown) {
            $this->markdown($content->markdown);
        }

        if ($content->htmlString) {
            $this->html($content->htmlString);
        }

        foreach ($content->with as $key => $value) {
            $this->with($key, $value);
        }
    }

    /**
     * Ensure the mailable's attachments are hydrated from the "attachments" method.
	 * 确保mailable的附件已从"attachments"方法中添加
     *
     * @return void
     */
    private function ensureAttachmentsAreHydrated()
    {
        if (! method_exists($this, 'attachments')) {
            return;
        }

        $attachments = $this->attachments();

        Collection::make(is_object($attachments) ? [$attachments] : $attachments)
            ->each(function ($attachment) {
                $this->attach($attachment);
            });
    }

    /**
     * Set the name of the mailer that should send the message.
	 * 设置应该发送邮件的邮件发件人的名称
     *
     * @param  string  $mailer
     * @return $this
     */
    public function mailer($mailer)
    {
        $this->mailer = $mailer;

        return $this;
    }

    /**
     * Register a callback to be called with the Symfony message instance.
	 * 注册一个要用Symfony消息实例调用的回调
     *
     * @param  callable  $callback
     * @return $this
     */
    public function withSymfonyMessage($callback)
    {
        $this->callbacks[] = $callback;

        return $this;
    }

    /**
     * Register a callback to be called while building the view data.
	 * 注册一个在构建视图数据时调用的回调
     *
     * @param  callable  $callback
     * @return void
     */
    public static function buildViewDataUsing(callable $callback)
    {
        static::$viewDataCallback = $callback;
    }

    /**
     * Dynamically bind parameters to the message.
	 * 动态地将参数绑定到消息
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return $this
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if (str_starts_with($method, 'with')) {
            return $this->with(Str::camel(substr($method, 4)), $parameters[0]);
        }

        static::throwBadMethodCallException($method);
    }
}
