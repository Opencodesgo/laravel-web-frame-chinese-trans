<?php
/**
 * GuzzleHttp，许诺，取消异常
 */

declare(strict_types=1);

namespace GuzzleHttp\Promise;

/**
 * Exception that is set as the reason for a promise that has been cancelled.
 * 这是一个被取消的承诺的原因
 */
class CancellationException extends RejectionException
{
}
