<?php
/**
 * Egulias，EmailValidator，结果，Result
 */

namespace Egulias\EmailValidator\Result;

interface Result
{
    /**
     * Is validation result valid?
     * 验证结果是否有效
     */
    public function isValid(): bool;

    /**
     * Is validation result invalid?
     * Usually the inverse of isValid()
     * 
     */
    public function isInvalid(): bool;

    /**
     * Short description of the result, human readable.
     * 结果的简短描述，人类可读。
     */
    public function description(): string;

    /**
     * Code for user land to act upon.
     * 供使用者土地采取行动的守则
     */
    public function code(): int;
}
