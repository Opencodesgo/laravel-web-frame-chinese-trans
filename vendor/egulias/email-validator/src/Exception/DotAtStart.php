<?php
/**
 * Egulias，电子邮件验证器，异常，点开始
 */

namespace Egulias\EmailValidator\Exception;

class DotAtStart extends InvalidEmail
{
    const CODE = 141;
    const REASON = "Found DOT at start";
}
