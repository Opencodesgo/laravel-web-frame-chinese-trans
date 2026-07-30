<?php
/**
 * Egulias，电子邮件验证器，异常，不公开的评论
 */

namespace Egulias\EmailValidator\Exception;

class UnopenedComment extends InvalidEmail
{
    const CODE = 152;
    const REASON = "No opening comment token found";
}
