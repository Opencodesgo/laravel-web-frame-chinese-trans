<?php
/**
 * Egulias，电子邮件验证器，异常，最后的CRLF
 */

namespace Egulias\EmailValidator\Exception;

class CRLFAtTheEnd extends InvalidEmail
{
    const CODE = 149;
    const REASON = "CRLF at the end";
}
