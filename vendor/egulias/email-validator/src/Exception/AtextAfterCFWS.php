<?php
/**
 * Egulias，EmailValidator，异常，Atext After CFWS
 */

namespace Egulias\EmailValidator\Exception;

class AtextAfterCFWS extends InvalidEmail
{
    const CODE = 133;
    const REASON = "ATEXT found after CFWS";
}
