<?php
/**
 * Symfony，Component，Mailer，运输，Smtp，认证，身份认证接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mailer\Transport\Smtp\Auth;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

/**
 * An Authentication mechanism.
 * 处理XOAUTH2身份验证。
 *
 * @author Chris Corbyn
 */
interface AuthenticatorInterface
{
    /**
     * Tries to authenticate the user.
	 * 尝试验证用户
     *
     * @throws TransportExceptionInterface
     */
    public function authenticate(EsmtpTransport $client): void;

    /**
     * Gets the name of the AUTH mechanism this Authenticator handles.
	 * 获取此身份验证器处理的AUTH机制的名称
     */
    public function getAuthKeyword(): string;
}
