<?php
/**
 * Egulias，电子邮件验证器，警告，地址文字
 */

namespace Egulias\EmailValidator\Warning;

class AddressLiteral extends Warning
{
    const CODE = 12;

    public function __construct()
    {
        $this->message = 'Address literal in domain part';
        $this->rfcNumber = 5321;
    }
}
