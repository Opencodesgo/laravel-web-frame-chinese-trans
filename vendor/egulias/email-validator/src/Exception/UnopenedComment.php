<?php
/**
 * Egulias，电子邮件验证器，异常，未打开的注释
 */

namespace Egulias\EmailValidator\Exception;

class UnopenedComment extends InvalidEmail
{
    const CODE = 152;
    const REASON = "No opening comment token found";
}
