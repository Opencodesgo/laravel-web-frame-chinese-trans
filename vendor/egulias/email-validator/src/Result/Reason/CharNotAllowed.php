<?php
/**
 * Egulias，EmailValidator，结果，原因，不允许Char
 */

namespace Egulias\EmailValidator\Result\Reason;

class CharNotAllowed implements Reason
{
    public function code() : int
    {
        return 1;
    }

    public function description() : string
    {
        return "Character not allowed";
    }
}
