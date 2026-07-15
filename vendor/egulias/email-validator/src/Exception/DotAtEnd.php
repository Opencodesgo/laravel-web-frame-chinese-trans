<?php
/**
 * Egulias，电子邮件验证器，异常，Dot At End
 */

namespace Egulias\EmailValidator\Exception;

class DotAtEnd extends InvalidEmail
{
    const CODE = 142;
    const REASON = "Dot at the end";
}
