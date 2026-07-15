<?php
/**
 * Egulias，电子邮件验证器，异常，无DNS记录
 */

namespace Egulias\EmailValidator\Exception;

class NoDNSRecord extends InvalidEmail
{
    const CODE = 5;
    const REASON = 'No MX or A DSN record was found for this email';
}
