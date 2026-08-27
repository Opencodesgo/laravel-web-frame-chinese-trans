<?php
/**
 * Egulias，EmailValidator，结果，原因，本地或保留域
 */

namespace Egulias\EmailValidator\Result\Reason;

class LocalOrReservedDomain implements Reason
{
    public function code() : int
    {
        return 153;
    }

    public function description() : string
    {
        return 'Local, mDNS or reserved domain (RFC2606, RFC6762)';
    }
}
