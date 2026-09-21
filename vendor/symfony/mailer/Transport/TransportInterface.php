<?php
/**
 * Symfony，Component，Mailer，运输，传输接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mailer\Transport;

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\RawMessage;

/**
 * Interface for all mailer transports.
 * 所有邮件传输的接口。
 *
 * When sending emails, you should prefer MailerInterface implementations
 * as they allow asynchronous sending.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface TransportInterface extends \Stringable
{
    /**
     * @throws TransportExceptionInterface
     */
    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage;
}
