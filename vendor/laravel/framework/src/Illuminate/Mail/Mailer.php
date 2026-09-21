<?php
/**
 * Illuminate，邮件，邮件程序
 */

namespace Illuminate\Mail;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Mail\Mailable as MailableContract;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Contracts\Mail\MailQueue as MailQueueContract;
use Illuminate\Contracts\Queue\Factory as QueueContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;

class Mailer implements MailerContract, MailQueueContract
{
    use Macroable;

    /**
     * The name that is configured for the mailer.
	 * 为邮件配置的名称
     *
     * @var string
     */
    protected $name;

    /**
     * The view factory instance.
	 * 视图工厂实例
     *
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $views;

    /**
     * The Symfony Transport instance.
	 * Symfony传输实例
     *
     * @var \Symfony\Component\Mailer\Transport\TransportInterface
     */
    protected $transport;

    /**
     * The event dispatcher instance.
	 * 事件调度程序实例
     *
     * @var \Illuminate\Contracts\Events\Dispatcher|null
     */
    protected $events;

    /**
     * The global from address and name.
	 * 全局从地址和名称
     *
     * @var array
     */
    protected $from;

    /**
     * The global reply-to address and name.
	 * 全局回复地址和名称
     *
     * @var array
     */
    protected $replyTo;

    /**
     * The global return path address.
	 * 全局返回路径地址
     *
     * @var array
     */
    protected $returnPath;

    /**
     * The global to address and name.
	 * 全局地址和名称
     *
     * @var array
     */
    protected $to;

    /**
     * The queue factory implementation.
	 * 队列工厂实现
     *
     * @var \Illuminate\Contracts\Queue\Factory
     */
    protected $queue;

    /**
     * Create a new Mailer instance.
	 * 创建一个新的Mailer实例
     *
     * @param  string  $name
     * @param  \Illuminate\Contracts\View\Factory  $views
     * @param  \Symfony\Component\Mailer\Transport\TransportInterface  $transport
     * @param  \Illuminate\Contracts\Events\Dispatcher|null  $events
     * @return void
     */
    public function __construct(string $name, Factory $views, TransportInterface $transport, Dispatcher $events = null)
    {
        $this->name = $name;
        $this->views = $views;
        $this->events = $events;
        $this->transport = $transport;
    }

    /**
     * Set the global from address and name.
	 * 设置全局的from地址和名称
     *
     * @param  string  $address
     * @param  string|null  $name
     * @return void
     */
    public function alwaysFrom($address, $name = null)
    {
        $this->from = compact('address', 'name');
    }

    /**
     * Set the global reply-to address and name.
	 * 设置全局回复地址和名称
     *
     * @param  string  $address
     * @param  string|null  $name
     * @return void
     */
    public function alwaysReplyTo($address, $name = null)
    {
        $this->replyTo = compact('address', 'name');
    }

    /**
     * Set the global return path address.
	 * 设置全局返回路径地址
     *
     * @param  string  $address
     * @return void
     */
    public function alwaysReturnPath($address)
    {
        $this->returnPath = compact('address');
    }

    /**
     * Set the global to address and name.
	 * 将全局变量设置为地址和名称
     *
     * @param  string  $address
     * @param  string|null  $name
     * @return void
     */
    public function alwaysTo($address, $name = null)
    {
        $this->to = compact('address', 'name');
    }

    /**
     * Begin the process of mailing a mailable class instance.
	 * 开始邮寄可邮寄类实例的过程
     *
     * @param  mixed  $users
     * @return \Illuminate\Mail\PendingMail
     */
    public function to($users)
    {
        return (new PendingMail($this))->to($users);
    }

    /**
     * Begin the process of mailing a mailable class instance.
	 * 开始邮寄可邮寄类实例的过程
     *
     * @param  mixed  $users
     * @return \Illuminate\Mail\PendingMail
     */
    public function cc($users)
    {
        return (new PendingMail($this))->cc($users);
    }

    /**
     * Begin the process of mailing a mailable class instance.
	 * 开始邮寄可邮寄类实例的过程
     *
     * @param  mixed  $users
     * @return \Illuminate\Mail\PendingMail
     */
    public function bcc($users)
    {
        return (new PendingMail($this))->bcc($users);
    }

    /**
     * Send a new message with only an HTML part.
	 * 发送只包含HTML部分的新消息
     *
     * @param  string  $html
     * @param  mixed  $callback
     * @return \Illuminate\Mail\SentMessage|null
     */
    public function html($html, $callback)
    {
        return $this->send(['html' => new HtmlString($html)], [], $callback);
    }

    /**
     * Send a new message with only a raw text part.
	 * 发送一个只有原始文本部分的新消息
     *
     * @param  string  $text
     * @param  mixed  $callback
     * @return \Illuminate\Mail\SentMessage|null
     */
    public function raw($text, $callback)
    {
        return $this->send(['raw' => $text], [], $callback);
    }

    /**
     * Send a new message with only a plain part.
	 * 发送一条只包含普通部分的新消息
     *
     * @param  string  $view
     * @param  array  $data
     * @param  mixed  $callback
     * @return \Illuminate\Mail\SentMessage|null
     */
    public function plain($view, array $data, $callback)
    {
        return $this->send(['text' => $view], $data, $callback);
    }

    /**
     * Render the given message as a view.
	 * 将给定的消息呈现为视图
     *
     * @param  string|array  $view
     * @param  array  $data
     * @return string
     */
    public function render($view, array $data = [])
    {
        // First we need to parse the view, which could either be a string or an array
        // containing both an HTML and plain text versions of the view which should
        // be used when sending an e-mail. We will extract both of them out here.
		// 首先，我们需要解析视图，它可以是字符串也可以是数组。
        [$view, $plain, $raw] = $this->parseView($view);

        $data['message'] = $this->createMessage();

        return $this->renderView($view ?: $plain, $data);
    }

    /**
     * Send a new message using a view.
	 * 使用视图发送新消息
     *
     * @param  \Illuminate\Contracts\Mail\Mailable|string|array  $view
     * @param  array  $data
     * @param  \Closure|string|null  $callback
     * @return \Illuminate\Mail\SentMessage|null
     */
    public function send($view, array $data = [], $callback = null)
    {
        if ($view instanceof MailableContract) {
            return $this->sendMailable($view);
        }

        $data['mailer'] = $this->name;

        // First we need to parse the view, which could either be a string or an array
        // containing both an HTML and plain text versions of the view which should
        // be used when sending an e-mail. We will extract both of them out here.
		// 首先，我们需要解析视图，它可以是字符串也可以是数组，包含HTML和纯文本版本。
        [$view, $plain, $raw] = $this->parseView($view);

        $data['message'] = $message = $this->createMessage();

        // Once we have retrieved the view content for the e-mail we will set the body
        // of this message using the HTML type, which will provide a simple wrapper
        // to creating view based emails that are able to receive arrays of data.
		// 一旦我们检索了电子邮件的视图内容，我们会设置HTML类型主体。
        if (! is_null($callback)) {
            $callback($message);
        }

        $this->addContent($message, $view, $plain, $raw, $data);

        // If a global "to" address has been set, we will set that address on the mail
        // message. This is primarily useful during local development in which each
        // message should be delivered into a single mail address for inspection.
		// 如果设置了全局"to"地址，我们将在邮件中设置该地址。
        if (isset($this->to['address'])) {
            $this->setGlobalToAndRemoveCcAndBcc($message);
        }

        // Next we will determine if the message should be sent. We give the developer
        // one final chance to stop this message and then we will send it to all of
        // its recipients. We will then fire the sent event for the sent message.
		// 接下来，我们将确定是否应该发送消息。
        $symfonyMessage = $message->getSymfonyMessage();

        if ($this->shouldSendMessage($symfonyMessage, $data)) {
            $symfonySentMessage = $this->sendSymfonyMessage($symfonyMessage);

            if ($symfonySentMessage) {
                $sentMessage = new SentMessage($symfonySentMessage);

                $this->dispatchSentEvent($sentMessage, $data);

                return $sentMessage;
            }
        }
    }

    /**
     * Send the given mailable.
	 * 发送给定的邮件
     *
     * @param  \Illuminate\Contracts\Mail\Mailable  $mailable
     * @return \Illuminate\Mail\SentMessage|null
     */
    protected function sendMailable(MailableContract $mailable)
    {
        return $mailable instanceof ShouldQueue
                        ? $mailable->mailer($this->name)->queue($this->queue)
                        : $mailable->mailer($this->name)->send($this);
    }

    /**
     * Parse the given view name or array.
	 * 解析给定的视图名或数组
     *
     * @param  string|array  $view
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function parseView($view)
    {
        if (is_string($view)) {
            return [$view, null, null];
        }

        // If the given view is an array with numeric keys, we will just assume that
        // both a "pretty" and "plain" view were provided, so we will return this
        // array as is, since it should contain both views with numerical keys.
		// 如果给定的视图是一个带有数字键的数组，我们只会假设。
        if (is_array($view) && isset($view[0])) {
            return [$view[0], $view[1], null];
        }

        // If this view is an array but doesn't contain numeric keys, we will assume
        // the views are being explicitly specified and will extract them via the
        // named keys instead, allowing the developers to use one or the other.
		// 如果这个视图是一个数组但不包含数字键。
        if (is_array($view)) {
            return [
                $view['html'] ?? null,
                $view['text'] ?? null,
                $view['raw'] ?? null,
            ];
        }

        throw new InvalidArgumentException('Invalid view.');
    }

    /**
     * Add the content to a given message.
	 * 将内容添加到给定消息中
     *
     * @param  \Illuminate\Mail\Message  $message
     * @param  string  $view
     * @param  string  $plain
     * @param  string  $raw
     * @param  array  $data
     * @return void
     */
    protected function addContent($message, $view, $plain, $raw, $data)
    {
        if (isset($view)) {
            $message->html($this->renderView($view, $data) ?: ' ');
        }

        if (isset($plain)) {
            $message->text($this->renderView($plain, $data) ?: ' ');
        }

        if (isset($raw)) {
            $message->text($raw);
        }
    }

    /**
     * Render the given view.
	 * 呈现给定的视图
     *
     * @param  string  $view
     * @param  array  $data
     * @return string
     */
    protected function renderView($view, $data)
    {
        return $view instanceof Htmlable
                        ? $view->toHtml()
                        : $this->views->make($view, $data)->render();
    }

    /**
     * Set the global "to" address on the given message.
	 * 在给定消息上设置全局"to"地址
     *
     * @param  \Illuminate\Mail\Message  $message
     * @return void
     */
    protected function setGlobalToAndRemoveCcAndBcc($message)
    {
        $message->forgetTo();

        $message->to($this->to['address'], $this->to['name'], true);

        $message->forgetCc();
        $message->forgetBcc();
    }

    /**
     * Queue a new e-mail message for sending.
	 * 将要发送的新电子邮件排队
     *
     * @param  \Illuminate\Contracts\Mail\Mailable|string|array  $view
     * @param  string|null  $queue
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function queue($view, $queue = null)
    {
        if (! $view instanceof MailableContract) {
            throw new InvalidArgumentException('Only mailables may be queued.');
        }

        if (is_string($queue)) {
            $view->onQueue($queue);
        }

        return $view->mailer($this->name)->queue($this->queue);
    }

    /**
     * Queue a new e-mail message for sending on the given queue.
	 * 将要在给定队列上发送的新电子邮件放入队列
     *
     * @param  string  $queue
     * @param  \Illuminate\Contracts\Mail\Mailable  $view
     * @return mixed
     */
    public function onQueue($queue, $view)
    {
        return $this->queue($view, $queue);
    }

    /**
     * Queue a new e-mail message for sending on the given queue.
	 * 将要在给定队列上发送的新电子邮件放入队列
     *
     * This method didn't match rest of framework's "onQueue" phrasing. Added "onQueue".
	 * 这个方法与框架的"onQueue"措辞不匹配。添加"onQueue"
     *
     * @param  string  $queue
     * @param  \Illuminate\Contracts\Mail\Mailable  $view
     * @return mixed
     */
    public function queueOn($queue, $view)
    {
        return $this->onQueue($queue, $view);
    }

    /**
     * Queue a new e-mail message for sending after (n) seconds.
	 * 等待(n)秒后发送新的电子邮件
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @param  \Illuminate\Contracts\Mail\Mailable  $view
     * @param  string|null  $queue
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function later($delay, $view, $queue = null)
    {
        if (! $view instanceof MailableContract) {
            throw new InvalidArgumentException('Only mailables may be queued.');
        }

        return $view->mailer($this->name)->later(
            $delay, is_null($queue) ? $this->queue : $queue
        );
    }

    /**
     * Queue a new e-mail message for sending after (n) seconds on the given queue.
	 * 在给定队列上等待(n)秒后发送的新电子邮件消息
     *
     * @param  string  $queue
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @param  \Illuminate\Contracts\Mail\Mailable  $view
     * @return mixed
     */
    public function laterOn($queue, $delay, $view)
    {
        return $this->later($delay, $view, $queue);
    }

    /**
     * Create a new message instance.
	 * 创建一个新的消息实例
     *
     * @return \Illuminate\Mail\Message
     */
    protected function createMessage()
    {
        $message = new Message(new Email());

        // If a global from address has been specified we will set it on every message
        // instance so the developer does not have to repeat themselves every time
        // they create a new message. We'll just go ahead and push this address.
		// 如果指定了全局from地址，我们将在每条消息上设置它。
        if (! empty($this->from['address'])) {
            $message->from($this->from['address'], $this->from['name']);
        }

        // When a global reply address was specified we will set this on every message
        // instance so the developer does not have to repeat themselves every time
        // they create a new message. We will just go ahead and push this address.
		// 当指定了全局回复地址时，我们将在每条消息上设置此地址实例。
        if (! empty($this->replyTo['address'])) {
            $message->replyTo($this->replyTo['address'], $this->replyTo['name']);
        }

        if (! empty($this->returnPath['address'])) {
            $message->returnPath($this->returnPath['address']);
        }

        return $message;
    }

    /**
     * Send a Symfony Email instance.
	 * 发送一个Symfony Email实例
     *
     * @param  \Symfony\Component\Mime\Email  $message
     * @return \Symfony\Component\Mailer\SentMessage|null
     */
    protected function sendSymfonyMessage(Email $message)
    {
        try {
            return $this->transport->send($message, Envelope::create($message));
        } finally {
            //
        }
    }

    /**
     * Determines if the email can be sent.
	 * 确定是否可以发送电子邮件
     *
     * @param  \Symfony\Component\Mime\Email  $message
     * @param  array  $data
     * @return bool
     */
    protected function shouldSendMessage($message, $data = [])
    {
        if (! $this->events) {
            return true;
        }

        return $this->events->until(
            new MessageSending($message, $data)
        ) !== false;
    }

    /**
     * Dispatch the message sent event.
	 * 分派消息发送事件
     *
     * @param  \Illuminate\Mail\SentMessage  $message
     * @param  array  $data
     * @return void
     */
    protected function dispatchSentEvent($message, $data = [])
    {
        if ($this->events) {
            $this->events->dispatch(
                new MessageSent($message, $data)
            );
        }
    }

    /**
     * Get the Symfony Transport instance.
	 * 获取Symfony Transport实例
     *
     * @return \Symfony\Component\Mailer\Transport\TransportInterface
     */
    public function getSymfonyTransport()
    {
        return $this->transport;
    }

    /**
     * Get the view factory instance.
	 * 获取视图工厂实例
     *
     * @return \Illuminate\Contracts\View\Factory
     */
    public function getViewFactory()
    {
        return $this->views;
    }

    /**
     * Set the Symfony Transport instance.
	 * 设置Symfony传输实例
     *
     * @param  \Symfony\Component\Mailer\Transport\TransportInterface  $transport
     * @return void
     */
    public function setSymfonyTransport(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Set the queue manager instance.
	 * 设置队列管理器实例
     *
     * @param  \Illuminate\Contracts\Queue\Factory  $queue
     * @return $this
     */
    public function setQueue(QueueContract $queue)
    {
        $this->queue = $queue;

        return $this;
    }
}
