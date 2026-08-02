<?php
/**
 * Whoops，异常，错误异常
 */

/**
 * Whoops - php errors for cool kids
 * @author Filipe Dobreira <http://github.com/filp>
 */

namespace Whoops\Exception;

use ErrorException as BaseErrorException;

/**
 * Wraps ErrorException; mostly used for typing (at least now)
 * to easily cleanup the stack trace of redundant info.
 * 包装异常；主要用于类型标注（至少目前），以便轻松清理堆栈跟踪中的冗余信息。
 */
class ErrorException extends BaseErrorException
{
}
