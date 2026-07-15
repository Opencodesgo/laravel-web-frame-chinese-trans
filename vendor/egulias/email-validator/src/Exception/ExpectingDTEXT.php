<?php
/**
 * Egulias，电子邮件验证器，异常，期望文本
 */

namespace Egulias\EmailValidator\Exception;

class ExpectingDTEXT extends InvalidEmail
{
    const CODE = 129;
    const REASON = "Expected DTEXT";
}
