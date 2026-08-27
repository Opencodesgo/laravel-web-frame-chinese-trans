<?php
/**
 * Egulias，EmailValidator，解析器，注释策略，注释策略
 */

namespace Egulias\EmailValidator\Parser\CommentStrategy;

use Egulias\EmailValidator\EmailLexer;
use Egulias\EmailValidator\Result\Result;
use Egulias\EmailValidator\Warning\Warning;

interface CommentStrategy
{
    /**
     * Return "true" to continue, "false" to exit
     */
    public function exitCondition(EmailLexer $lexer, int $openedParenthesis): bool;

    public function endOfLoopValidations(EmailLexer $lexer): Result;

    /**
     * @return Warning[]
     */
    public function getWarnings(): array;
}
