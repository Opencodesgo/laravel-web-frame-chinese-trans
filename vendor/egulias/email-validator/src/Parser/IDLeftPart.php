<?php
/**
 * Egulias，EmailValidator，解析器，ID 左部分
 */

namespace Egulias\EmailValidator\Parser;

use Egulias\EmailValidator\Result\Result;
use Egulias\EmailValidator\Result\InvalidEmail;
use Egulias\EmailValidator\Result\Reason\CommentsInIDRight;

class IDLeftPart extends LocalPart
{
    protected function parseComments(): Result
    {
        return new InvalidEmail(new CommentsInIDRight(), $this->lexer->current->value);
    }
}
