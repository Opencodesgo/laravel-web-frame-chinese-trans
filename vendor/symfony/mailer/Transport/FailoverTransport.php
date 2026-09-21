<?php
/**
 * Symfony，Component，Mailer，运输，Failover 运输
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

/**
 * Uses several Transports using a failover algorithm.
 * 使用故障转移算法使用多个传输。
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class FailoverTransport extends RoundRobinTransport
{
    private ?TransportInterface $currentTransport = null;

    protected function getNextTransport(): ?TransportInterface
    {
        if (null === $this->currentTransport || $this->isTransportDead($this->currentTransport)) {
            $this->currentTransport = parent::getNextTransport();
        }

        return $this->currentTransport;
    }

    protected function getInitialCursor(): int
    {
        return 0;
    }

    protected function getNameSymbol(): string
    {
        return 'failover';
    }
}
