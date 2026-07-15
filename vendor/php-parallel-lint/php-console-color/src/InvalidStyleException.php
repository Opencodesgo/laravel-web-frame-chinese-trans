<?php
/**
 * JakubOnderka，Php控制台颜色，无效样式异常
 */

namespace JakubOnderka\PhpConsoleColor;

class InvalidStyleException extends \Exception
{
    public function __construct($styleName)
    {
        parent::__construct("Invalid style $styleName.");
    }
}