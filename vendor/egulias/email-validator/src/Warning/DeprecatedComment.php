<?php
/**
 * Egulias，电子邮件验证器，警告，不赞成的评论
 */

namespace Egulias\EmailValidator\Warning;

class DeprecatedComment extends Warning
{
    const CODE = 37;

    public function __construct()
    {
        $this->message = 'Deprecated comments';
    }
}
