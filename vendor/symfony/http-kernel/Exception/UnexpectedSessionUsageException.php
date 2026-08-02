<?php
/**
 * Symfony，Component，HttpKernel，异常，意料之外会话使用异常
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\Exception;

/**
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 */
class UnexpectedSessionUsageException extends \LogicException
{
}
