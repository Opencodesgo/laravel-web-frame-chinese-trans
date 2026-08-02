<?php
/**
 * Egulias，电子邮件验证器，异常，连续点
 */

namespace Egulias\EmailValidator\Exception;

class ConsecutiveDot extends InvalidEmail
{
    const CODE = 132;
    const REASON = "Consecutive DOT";
}
