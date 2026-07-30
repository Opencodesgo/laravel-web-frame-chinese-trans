<?php
/**
 * Egulias，电子邮件验证器，异常，不允许的字符
 */

namespace Egulias\EmailValidator\Exception;

class CharNotAllowed extends InvalidEmail
{
    const CODE = 201;
    const REASON = "Non allowed character in domain";
}
