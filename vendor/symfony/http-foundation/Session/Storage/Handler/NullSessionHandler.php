<?php
/**
 * Symfony，Component，HttpFoundation，会话，存储，处理器，空会话处理程序
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\Session\Storage\Handler;

/**
 * Can be used in unit testing or in a situations where persisted sessions are not desired.
 * 可用于单元测试或不需要持久会话的情况。
 *
 * @author Drak <drak@zikula.org>
 */
class NullSessionHandler extends AbstractSessionHandler
{
    public function close(): bool
    {
        return true;
    }

    public function validateId(#[\SensitiveParameter] string $sessionId): bool
    {
        return true;
    }

    protected function doRead(#[\SensitiveParameter] string $sessionId): string
    {
        return '';
    }

    public function updateTimestamp(#[\SensitiveParameter] string $sessionId, string $data): bool
    {
        return true;
    }

    protected function doWrite(#[\SensitiveParameter] string $sessionId, string $data): bool
    {
        return true;
    }

    protected function doDestroy(#[\SensitiveParameter] string $sessionId): bool
    {
        return true;
    }

    public function gc(int $maxlifetime): int|false
    {
        return 0;
    }
}
