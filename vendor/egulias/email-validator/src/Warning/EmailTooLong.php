<?php
/**
 * Egulias，电子邮件验证器，警告，电子邮件太长
 */

namespace Egulias\EmailValidator\Warning;

use Egulias\EmailValidator\EmailParser;

class EmailTooLong extends Warning
{
    const CODE = 66;

    public function __construct()
    {
        $this->message = 'Email is too long, exceeds ' . EmailParser::EMAIL_MAX_LENGTH;
    }
}
