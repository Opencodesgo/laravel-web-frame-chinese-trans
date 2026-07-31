<?php
/**
 * Egulias，EmailValidator，验证，Email 验证
 */

namespace Egulias\EmailValidator\Validation;

use Egulias\EmailValidator\EmailLexer;
use Egulias\EmailValidator\Exception\InvalidEmail;
use Egulias\EmailValidator\Warning\Warning;

interface EmailValidation
{
    /**
     * Returns true if the given email is valid.
	 * 如果给定的电子邮件是有效的,返回true
     *
     * @param string     $email      The email you want to validate.
     * @param EmailLexer $emailLexer The email lexer.
     *
     * @return bool
     */
    public function isValid($email, EmailLexer $emailLexer);

    /**
     * Returns the validation error.
	 * 返回验证错误
     *
     * @return InvalidEmail|null
     */
    public function getError();

    /**
     * Returns the validation warnings.
	 * 返回验证警告
     *
     * @return Warning[]
     */
    public function getWarnings();
}
