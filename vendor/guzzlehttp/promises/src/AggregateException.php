<?php
/**
 * GuzzleHttp，许诺，聚合异常
 */

declare(strict_types=1);

namespace GuzzleHttp\Promise;

/**
 * Exception thrown when too many errors occur in the some() or any() methods.
 * 当在一些()或任何()方法中出现过多的错误时,会抛出异常
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
