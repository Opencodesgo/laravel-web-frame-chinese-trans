<?php
/**
 * Egulias，EmailValidator，结果，原因，正文CFWS 之后
 */

namespace Egulias\EmailValidator\Result\Reason;

class AtextAfterCFWS implements Reason
{
    public function code() : int
    {
        return 133;
    }

    public function description() : string
    {
        return 'ATEXT found after CFWS';
    }
}
