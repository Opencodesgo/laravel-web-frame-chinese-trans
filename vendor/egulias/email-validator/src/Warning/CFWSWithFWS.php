<?php
/**
 * Egulias，电子邮件验证器，警告，CFWSWithFWS
 */

namespace Egulias\EmailValidator\Warning;

class CFWSWithFWS extends Warning
{
    const CODE = 18;

    public function __construct()
    {
        $this->message = 'Folding whites space followed by folding white space';
    }
}
