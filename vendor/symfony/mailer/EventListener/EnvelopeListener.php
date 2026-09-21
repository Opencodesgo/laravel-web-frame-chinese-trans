<?php
/**
 * Symfony，Component，Mailer，事件监听器，信封侦听器
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mailer\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Message;

/**
 * Manipulates the Envelope of a Message.
 * 操作消息的信封。
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class EnvelopeListener implements EventSubscriberInterface
{
    private ?Address $sender = null;

    /**
     * @var Address[]|null
     */
    private ?array $recipients = null;

    /**
     * @param array<Address|string> $recipients
     */
    public function __construct(Address|string|null $sender = null, ?array $recipients = null)
    {
        if (null !== $sender) {
            $this->sender = Address::create($sender);
        }
        if (null !== $recipients) {
            $this->recipients = Address::createArray($recipients);
        }
    }

    public function onMessage(MessageEvent $event): void
    {
        if ($this->sender) {
            $event->getEnvelope()->setSender($this->sender);

            $message = $event->getMessage();
            if ($message instanceof Message) {
                if (!$message->getHeaders()->has('Sender') && !$message->getHeaders()->has('From')) {
                    $message->getHeaders()->addMailboxHeader('Sender', $this->sender);
                }
            }
        }

        if ($this->recipients) {
            $event->getEnvelope()->setRecipients($this->recipients);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // should be the last one to allow header changes by other listeners first
			// 应该是最后一个允许其他侦听器首先更改标题的侦听器吗
            MessageEvent::class => ['onMessage', -255],
        ];
    }
}
