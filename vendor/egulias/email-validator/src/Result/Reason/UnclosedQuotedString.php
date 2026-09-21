<?php
/**
 * Egulias，EmailValidator，结果，原因，未关闭引号字符串
 */

namespace Egulias\EmailValidator\Result\Reason;

class UnclosedQuotedString implements Reason
{
    public function code() : int
    {
        return 145;
    }

    public function description() : string
    {
        return "Unclosed quoted string";
    }
}
