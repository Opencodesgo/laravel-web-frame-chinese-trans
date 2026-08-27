<?php
/**
 * Egulias，EmailValidator，结果，原因，Spoof 邮件
 */

namespace Egulias\EmailValidator\Result\Reason;

class SpoofEmail implements Reason
{
    public function code() : int
    {
        return 298;
    }

    public function description() : string
    {
        return 'The email contains mixed UTF8 chars that makes it suspicious'; 
    }

}
