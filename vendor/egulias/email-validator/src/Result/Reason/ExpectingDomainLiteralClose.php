<?php
/**
 * Egulias，EmailValidator，结果，原因，期望域文字关闭
 */

namespace Egulias\EmailValidator\Result\Reason;

class ExpectingDomainLiteralClose implements Reason
{
    public function code() : int
    {
        return 137;
    }

    public function description() : string
    {
        return "Closing bracket ']' for domain literal not found";
    }
}
