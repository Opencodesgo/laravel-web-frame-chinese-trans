<?php
/**
 * Egulias，EmailValidator，警告，地址常量
 */

namespace Egulias\EmailValidator\Warning;

class AddressLiteral extends Warning
{
    public const CODE = 12;

    public function __construct()
    {
        $this->message = 'Address literal in domain part';
        $this->rfcNumber = 5321;
    }
}
