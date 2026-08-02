<?php
/**
 * Egulias，电子邮件验证器，异常，UnclosedQuotedString
 */

namespace Egulias\EmailValidator\Exception;

class UnclosedQuotedString extends InvalidEmail
{
    const CODE = 145;
    const REASON = "Unclosed quoted string";
}
