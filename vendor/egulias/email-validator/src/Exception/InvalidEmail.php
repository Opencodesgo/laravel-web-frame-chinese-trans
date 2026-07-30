<?php
/**
 * Egulias，电子邮件验证器，异常，无效电子邮件
 */

namespace Egulias\EmailValidator\Exception;

abstract class InvalidEmail extends \InvalidArgumentException
{
    const REASON = "Invalid email";
    const CODE = 0;

    public function __construct()
    {
        parent::__construct(static::REASON, static::CODE);
    }
}
