<?php
/**
 * Illuminate，邮件，发送队列 Mailable
 */

namespace Illuminate\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Factory as MailFactory;
use Illuminate\Contracts\Mail\Mailable as MailableContract;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Queue\InteractsWithQueue;

class SendQueuedMailable
{
    use Queueable, InteractsWithQueue;

    /**
     * The mailable message instance.
	 * 可邮件消息实例
     *
     * @var \Illuminate\Contracts\Mail\Mailable
     */
    public $mailable;

    /**
     * The number of times the job may be attempted.
	 * 可能尝试该作业的次数
     *
     * @var int
     */
    public $tries;

    /**
     * The number of seconds the job can run before timing out.
	 * 作业在超时之前可以运行的秒数
     *
     * @var int
     */
    public $timeout;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
	 * 在失败之前允许的未处理异常的最大数量
     *
     * @return int|null
     */
    public $maxExceptions;

    /**
     * Indicates if the job should be encrypted.
	 * 指示作业是否应该加
     *
     * @var bool
     */
    public $shouldBeEncrypted = false;

    /**
     * Create a new job instance.
	 * 创建新的作业实例
     *
     * @param  \Illuminate\Contracts\Mail\Mailable  $mailable
     * @return void
     */
    public function __construct(MailableContract $mailable)
    {
        $this->mailable = $mailable;
        $this->tries = property_exists($mailable, 'tries') ? $mailable->tries : null;
        $this->timeout = property_exists($mailable, 'timeout') ? $mailable->timeout : null;
        $this->maxExceptions = property_exists($mailable, 'maxExceptions') ? $mailable->maxExceptions : null;
        $this->afterCommit = property_exists($mailable, 'afterCommit') ? $mailable->afterCommit : null;
        $this->shouldBeEncrypted = $mailable instanceof ShouldBeEncrypted;
    }

    /**
     * Handle the queued job.
	 * 处理排队作业
     *
     * @param  \Illuminate\Contracts\Mail\Factory  $factory
     * @return void
     */
    public function handle(MailFactory $factory)
    {
        $this->mailable->send($factory);
    }

    /**
     * Get the number of seconds before a released mailable will be available.
	 * 获取发布邮件可用前的秒数。
     *
     * @return mixed
     */
    public function backoff()
    {
        if (! method_exists($this->mailable, 'backoff') && ! isset($this->mailable->backoff)) {
            return;
        }

        return $this->mailable->backoff ?? $this->mailable->backoff();
    }

    /**
     * Determine the time at which the job should timeout.
	 * 确定作业应该超时的时间
     *
     * @return \DateTime|null
     */
    public function retryUntil()
    {
        if (! method_exists($this->mailable, 'retryUntil') && ! isset($this->mailable->retryUntil)) {
            return;
        }

        return $this->mailable->retryUntil ?? $this->mailable->retryUntil();
    }

    /**
     * Call the failed method on the mailable instance.
	 * 在可邮件实例上调用失败的方法
     *
     * @param  \Throwable  $e
     * @return void
     */
    public function failed($e)
    {
        if (method_exists($this->mailable, 'failed')) {
            $this->mailable->failed($e);
        }
    }

    /**
     * Get the display name for the queued job.
	 * 获取排队作业的显示名称
     *
     * @return string
     */
    public function displayName()
    {
        return get_class($this->mailable);
    }

    /**
     * Prepare the instance for cloning.
	 * 为克隆准备实例
     *
     * @return void
     */
    public function __clone()
    {
        $this->mailable = clone $this->mailable;
    }
}
