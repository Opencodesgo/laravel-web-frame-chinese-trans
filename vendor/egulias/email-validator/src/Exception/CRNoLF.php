<?php
/**
 * Egulias，电子邮件验证器，异常，CR No LF
 */

namespace Egulias\EmailValidator\Exception;

class CRNoLF extends InvalidEmail
{
    const CODE = 150;
    const REASON = "Missing LF after CR";
}
