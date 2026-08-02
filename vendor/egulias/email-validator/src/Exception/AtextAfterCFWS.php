<?php
/**
 * Egulias，电子邮件验证器，异常，Atext After CFWS
 */

namespace Egulias\EmailValidator\Exception;

class AtextAfterCFWS extends InvalidEmail
{
    const CODE = 133;
    const REASON = "ATEXT found after CFWS";
}
