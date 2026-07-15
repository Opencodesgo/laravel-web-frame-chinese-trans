<?php
/**
 * Egulias，电子邮件验证器，警告，IPV6 冒号结束
 */

namespace Egulias\EmailValidator\Warning;

class IPV6ColonEnd extends Warning
{
    const CODE = 77;

    public function __construct()
    {
        $this->message = ':: found at the end of the domain literal';
        $this->rfcNumber = 5322;
    }
}
