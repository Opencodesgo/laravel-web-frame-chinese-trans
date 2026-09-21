<?php
/**
 * GuzzleHttp，Promise，总异常
 */

declare(strict_types=1);

namespace GuzzleHttp\Promise;

/**
 * Exception thrown when too many errors occur in the some() or any() methods.
 * 当在some()或any()方法中发生过多错误时引发异常
 */
class AggregateException extends RejectionException
{
    public function __construct(string $msg, array $reasons)
    {
        parent::__construct(
            $reasons,
            sprintf('%s; %d rejected promises', $msg, count($reasons))
        );
    }
}
