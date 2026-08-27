<?php
/**
 * Symfony，Component，Mailer，运输，Native 传输工厂
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

use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\UnsupportedSchemeException;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;

/**
 * Factory that configures a transport (sendmail or SMTP) based on php.ini settings.
 * 基于php.ini设置配置传输（sendmail或SMTP）的工厂。
 *
 * @author Laurent VOULLEMIER <laurent.voullemier@gmail.com>
 */
final class NativeTransportFactory extends AbstractTransportFactory
{
    public function create(Dsn $dsn): TransportInterface
    {
        if (!\in_array($dsn->getScheme(), $this->getSupportedSchemes(), true)) {
            throw new UnsupportedSchemeException($dsn, 'native', $this->getSupportedSchemes());
        }

        if ($sendMailPath = ini_get('sendmail_path')) {
            return new SendmailTransport($sendMailPath, $this->dispatcher, $this->logger);
        }

        if ('\\' !== \DIRECTORY_SEPARATOR) {
            throw new TransportException('sendmail_path is not configured in php.ini.');
        }

        // Only for windows hosts; at this point non-windows
        // host have already thrown an exception or returned a transport
		// 仅适用于windows主机；此时非窗口
		// 主机已抛出异常或返回传输
        $host = ini_get('SMTP');
        $port = (int) ini_get('smtp_port');

        if (!$host || !$port) {
            throw new TransportException('smtp or smtp_port is not configured in php.ini.');
        }

        $socketStream = new SocketStream();
        $socketStream->setHost($host);
        $socketStream->setPort($port);
        if (465 !== $port) {
            $socketStream->disableTls();
        }

        return new SmtpTransport($socketStream, $this->dispatcher, $this->logger);
    }

    protected function getSupportedSchemes(): array
    {
        return ['native'];
    }
}
