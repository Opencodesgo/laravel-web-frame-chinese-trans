<?php
/**
 * Illuminate，邮件，传输，Log 传输
 */

namespace Illuminate\Mail\Transport;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\RawMessage;

class LogTransport implements TransportInterface
{
    /**
     * The Logger instance.
	 * Logger实例
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Create a new log transport instance.
	 * 创建一个新的日志传输实例
     *
     * @param  \Psr\Log\LoggerInterface  $logger
     * @return void
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function send(RawMessage $message, Envelope $envelope = null): ?SentMessage
    {
        $this->logger->debug($message->toString());

        return new SentMessage($message, $envelope ?? Envelope::create($message));
    }

    /**
     * Get the logger for the LogTransport instance.
	 * 获取LogTransport实例的记录器
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function logger()
    {
        return $this->logger;
    }

    /**
     * Get the string representation of the transport.
	 * 获取传输的字符串表示形式
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'log';
    }
}
