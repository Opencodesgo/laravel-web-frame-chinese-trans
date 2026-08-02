<?php
/**
 * Egulias，电子邮件验证器，异常，未关闭的评论
 */

namespace Egulias\EmailValidator\Exception;

class UnclosedComment extends InvalidEmail
{
    const CODE = 146;
    const REASON = "No closing comment token found";
}
