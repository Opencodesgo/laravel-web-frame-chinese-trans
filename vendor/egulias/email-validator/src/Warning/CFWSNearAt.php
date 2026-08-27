<?php
/**
 * Egulias，EmailValidator，警告，CFWS Near At
 */

namespace Egulias\EmailValidator\Warning;

class CFWSNearAt extends Warning
{
    public const CODE = 49;

    public function __construct()
    {
        $this->message = "Deprecated folding white space near @";
    }
}
