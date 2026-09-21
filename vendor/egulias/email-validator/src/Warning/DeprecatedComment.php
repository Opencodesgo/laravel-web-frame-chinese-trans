<?php
/**
 * Egulias，EmailValidator，警告，弃用的评论
 */

namespace Egulias\EmailValidator\Warning;

class DeprecatedComment extends Warning
{
    public const CODE = 37;

    public function __construct()
    {
        $this->message = 'Deprecated comments';
    }
}
