<?php
/**
 * Symfony，Component，Mailer，邮件收发机接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mailer;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\RawMessage;

/**
 * Interface for mailers able to send emails synchronously and/or asynchronously.
 * 能够同步和/或异步发送电子邮件的邮件发送者的接口。
 *
 * Implementations must support synchronous and asynchronous sending.
 * 实现必须支持同步和异步发送。
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface MailerInterface
{
    /**
     * @throws TransportExceptionInterface
     */
    public function send(RawMessage $message, ?Envelope $envelope = null): void;
}
