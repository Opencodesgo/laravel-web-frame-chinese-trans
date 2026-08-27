<?php
/**
 * Carbon，异常，不支持语言环境的异常 
 */

/**
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Carbon\Exceptions;

use InvalidArgumentException as BaseInvalidArgumentException;
use Throwable;

class NotLocaleAwareException extends BaseInvalidArgumentException implements InvalidArgumentException
{
    /**
     * Constructor.
	 * 构造方法
     *
     * @param mixed          $object
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($object, $code = 0, ?Throwable $previous = null)
    {
        $dump = \is_object($object) ? \get_class($object) : \gettype($object);

        parent::__construct("$dump does neither implements Symfony\Contracts\Translation\LocaleAwareInterface nor getLocale() method.", $code, $previous);
    }
}
