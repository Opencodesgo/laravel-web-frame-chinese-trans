<?php
/**
 * Egulias，EmailValidator，异常，连续的点
 */

namespace Egulias\EmailValidator\Exception;

class ConsecutiveDot extends InvalidEmail
{
    const CODE = 132;
    const REASON = "Consecutive DOT";
}
