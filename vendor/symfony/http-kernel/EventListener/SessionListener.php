<?php
/**
 * Symfony，Component，HttpKernel，事件监听器，会话侦听器
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\EventListener;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Sets the session in the request.
 * 设置请求中的会话。
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @final
 */
class SessionListener extends AbstractSessionListener
{
    protected function getSession(): ?SessionInterface
    {
        if ($this->container->has('session_factory')) {
            return $this->container->get('session_factory')->createSession();
        }

        return null;
    }
}
