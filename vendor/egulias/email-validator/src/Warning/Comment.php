<?php
/**
 * Egulias，EmailValidator，警告，注释
 */

namespace Egulias\EmailValidator\Warning;

class Comment extends Warning
{
    public const CODE = 17;

    public function __construct()
    {
        $this->message = "Comments found in this email";
    }
}
