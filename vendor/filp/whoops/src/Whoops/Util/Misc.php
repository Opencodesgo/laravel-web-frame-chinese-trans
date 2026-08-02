<?php
/**
 * Whoops，工具，混杂的
 */

/**
 * Whoops - php errors for cool kids
 * @author Filipe Dobreira <http://github.com/filp>
 */

namespace Whoops\Util;

class Misc
{
    /**
     * Can we at this point in time send HTTP headers?
	 * 在这个时候,我们能发送HTTP头吗?
     *
     * Currently this checks if we are even serving an HTTP request,
     * as opposed to running from a command line.
     *
     * If we are serving an HTTP request, we check if it's not too late.
     *
     * @return bool
     */
    public static function canSendHeaders()
    {
        return isset($_SERVER["REQUEST_URI"]) && !headers_sent();
    }

    public static function isAjaxRequest()
    {
        return (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    /**
     * Check, if possible, that this execution was triggered by a command line.
	 * 如果可能的话,检查这个执行是由命令行触发的。
     * @return bool
     */
    public static function isCommandLine()
    {
        return PHP_SAPI == 'cli';
    }

    /**
     * Translate ErrorException code into the represented constant.
	 * 将ErrorException代码转换为表示的常量
     *
     * @param int $error_code
     * @return string
     */
    public static function translateErrorCode($error_code)
    {
        $constants = get_defined_constants(true);
        if (array_key_exists('Core', $constants)) {
            foreach ($constants['Core'] as $constant => $value) {
                if (substr($constant, 0, 2) == 'E_' && $value == $error_code) {
                    return $constant;
                }
            }
        }
        return "E_UNKNOWN";
    }
    
    /**
     * Determine if an error level is fatal (halts execution)
	 * 确定错误级别是否为致命(halts执行)
     *
     * @param int $level
     * @return bool
     */
    public static function isLevelFatal($level)
    {
        $errors = E_ERROR;
        $errors |= E_PARSE;
        $errors |= E_CORE_ERROR;
        $errors |= E_CORE_WARNING;
        $errors |= E_COMPILE_ERROR;
        $errors |= E_COMPILE_WARNING;
        return ($level & $errors) > 0;
    }
}
