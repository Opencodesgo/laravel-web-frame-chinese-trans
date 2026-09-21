<?php
/**
 * Psy，异常，运行时异常
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
 * A RuntimeException for Psy.
 * 一个运行时异常的Psy。
 */
class RuntimeException extends \RuntimeException implements Exception
{
    private string $rawMessage;

    /**
     * Make this bad boy.
	 * 让这个坏男孩
     *
     * @param string          $message  (default: "")
     * @param int             $code     (default: 0)
     * @param \Throwable|null $previous (default: null)
     */
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        $this->rawMessage = $message;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Return a raw (unformatted) version of the error message.
	 * 返回错误消息的原始（未格式化）版本
     */
    public function getRawMessage(): string
    {
        return $this->rawMessage;
    }
}
