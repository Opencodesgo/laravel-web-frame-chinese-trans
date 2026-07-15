<?php
/**
 * Egulias，电子邮件验证器，异常，Consecutive At
 */

namespace Egulias\EmailValidator\Exception;

class ConsecutiveAt extends InvalidEmail
{
    const CODE = 128;
    const REASON = "Consecutive AT";
}
