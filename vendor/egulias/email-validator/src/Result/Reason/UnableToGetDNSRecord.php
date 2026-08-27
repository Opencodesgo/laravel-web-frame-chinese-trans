<?php
/**
 * Egulias，EmailValidator，结果，原因，无法获取DNS记录
 */

namespace Egulias\EmailValidator\Result\Reason;

/**
 * Used on SERVFAIL, TIMEOUT or other runtime and network errors
 * 用于SERVFAIL，TIMEOUT或其他运行时和网络错误。
 */
class UnableToGetDNSRecord extends NoDNSRecord
{
    public function code() : int
    {
        return 3;
    }

    public function description() : string
    {
        return 'Unable to get DNS records for the host';
    }
}
