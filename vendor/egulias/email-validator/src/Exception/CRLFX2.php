<?php
/**
 * Egulias，电子邮件验证器，异常，CRLFX2
 */

namespace Egulias\EmailValidator\Exception;

class CRLFX2 extends InvalidEmail
{
    const CODE = 148;
    const REASON = "Folding whitespace CR LF found twice";
}
