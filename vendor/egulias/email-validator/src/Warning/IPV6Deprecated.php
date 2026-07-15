<?php
/**
 * Egulias，电子邮件验证器，警告，IPV6弃用
 */

namespace Egulias\EmailValidator\Warning;

class IPV6Deprecated extends Warning
{
    const CODE = 13;

    public function __construct()
    {
        $this->message = 'Deprecated form of IPV6';
        $this->rfcNumber = 5321;
    }
}
