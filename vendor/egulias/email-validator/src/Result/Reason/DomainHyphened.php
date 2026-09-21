<?php
/**
 * Egulias，EmailValidator，结果，原因，域字符
 */

namespace Egulias\EmailValidator\Result\Reason;

class DomainHyphened extends DetailedReason
{
    public function code() : int
    {
        return 144;
    }

    public function description() : string
    {
        return 'S_HYPHEN found in domain';
    }
}
