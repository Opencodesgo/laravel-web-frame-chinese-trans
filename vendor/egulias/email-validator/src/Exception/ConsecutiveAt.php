<?php
/**
 * Egulias，EmailValidator，异常，Consecutive At
 */

namespace Egulias\EmailValidator\Exception;

class ConsecutiveAt extends InvalidEmail
{
    const CODE = 128;
    const REASON = "Consecutive AT";
}
