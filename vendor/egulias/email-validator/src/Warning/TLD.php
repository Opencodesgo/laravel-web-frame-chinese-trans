<?php
/**
 * Egulias，电子邮件验证器，警告，Warning
 */

namespace Egulias\EmailValidator\Warning;

class TLD extends Warning
{
    const CODE = 9;

    public function __construct()
    {
        $this->message = "RFC5321, TLD";
    }
}
