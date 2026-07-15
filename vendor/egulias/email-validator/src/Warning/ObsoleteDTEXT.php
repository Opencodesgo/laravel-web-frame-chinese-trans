<?php
/**
 * Egulias，电子邮件验证器，警告，过时的 DTEXT
 */

namespace Egulias\EmailValidator\Warning;

class ObsoleteDTEXT extends Warning
{
    const CODE = 71;

    public function __construct()
    {
        $this->rfcNumber = 5322;
        $this->message = 'Obsolete DTEXT in domain literal';
    }
}
