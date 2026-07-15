<?php
/**
 * Egulias，电子邮件验证器，警告，域文字
 */

namespace Egulias\EmailValidator\Warning;

class DomainLiteral extends Warning
{
    const CODE = 70;

    public function __construct()
    {
        $this->message = 'Domain Literal';
        $this->rfcNumber = 5322;
    }
}
