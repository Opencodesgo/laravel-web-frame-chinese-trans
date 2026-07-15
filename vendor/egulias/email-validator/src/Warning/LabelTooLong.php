<?php
/**
 * Egulias，电子邮件验证器，警告，标签太长
 */

namespace Egulias\EmailValidator\Warning;

class LabelTooLong extends Warning
{
    const CODE = 63;

    public function __construct()
    {
        $this->message = 'Label too long';
        $this->rfcNumber = 5322;
    }
}
