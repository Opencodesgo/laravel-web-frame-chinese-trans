<?php
/**
 * Egulias，电子邮件验证器，异常，期待 CTEXT
 */

namespace Egulias\EmailValidator\Exception;

class ExpectingCTEXT extends InvalidEmail
{
    const CODE = 139;
    const REASON = "Expecting CTEXT";
}
