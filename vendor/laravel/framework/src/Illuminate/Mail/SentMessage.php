<?php
/**
 * Illuminate，邮件，已发信息
 */

namespace Illuminate\Mail;

use Illuminate\Support\Traits\ForwardsCalls;
use Symfony\Component\Mailer\SentMessage as SymfonySentMessage;

/**
 * @mixin \Symfony\Component\Mailer\SentMessage
 */
class SentMessage
{
    use ForwardsCalls;

    /**
     * The Symfony SentMessage instance.
	 * Symfony SentMessage实例
     *
     * @var \Symfony\Component\Mailer\SentMessage
     */
    protected $sentMessage;

    /**
     * Create a new SentMessage instance.
	 * 创建一个新的SentMessage实例
     *
     * @param  \Symfony\Component\Mailer\SentMessage  $sentMessage
     * @return void
     */
    public function __construct(SymfonySentMessage $sentMessage)
    {
        $this->sentMessage = $sentMessage;
    }

    /**
     * Get the underlying Symfony Email instance.
	 * 获取底层的Symfony Email实例
     *
     * @return \Symfony\Component\Mailer\SentMessage
     */
    public function getSymfonySentMessage()
    {
        return $this->sentMessage;
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
        return $this->forwardCallTo($this->sentMessage, $method, $parameters);
    }
}
