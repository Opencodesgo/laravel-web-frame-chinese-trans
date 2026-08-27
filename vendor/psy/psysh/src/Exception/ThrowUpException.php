<?php
/**
 * Psy，异常，抛出异常
 */

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2023 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\Exception;

/**
 * A throw-up exception, used for throwing an exception out of the Psy Shell.
 * 抛出异常，用于从Psy Shell抛出异常。
 */
class ThrowUpException extends \Exception implements Exception
{
    /**
     * {@inheritdoc}
     */
    public function __construct(\Throwable $throwable)
    {
        $message = \sprintf("Throwing %s with message '%s'", \get_class($throwable), $throwable->getMessage());
        parent::__construct($message, $throwable->getCode(), $throwable);
    }

    /**
     * Return a raw (unformatted) version of the error message.
	 * 返回错误消息的原始（未格式化）版本
     */
    public function getRawMessage(): string
    {
        return $this->getPrevious()->getMessage();
    }

    /**
     * Create a ThrowUpException from a Throwable.
	 * 从Throwable中创建一个ThrowUpException
     *
     * @deprecated PsySH no longer wraps Throwables
     *
     * @param \Throwable $throwable
     */
    public static function fromThrowable($throwable)
    {
        @\trigger_error('PsySH no longer wraps Throwables', \E_USER_DEPRECATED);
    }
}
