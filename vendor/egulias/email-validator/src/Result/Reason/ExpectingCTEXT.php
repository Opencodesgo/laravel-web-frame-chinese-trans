<?php
/**
 * Egulias，EmailValidator，结果，原因，期待CTEXT
 */

namespace Egulias\EmailValidator\Result\Reason;

class ExpectingCTEXT implements Reason
{
    public function code() : int
    {
        return 139;
    }

    public function description() : string
    {
        return 'Expecting CTEXT';
    }
}
