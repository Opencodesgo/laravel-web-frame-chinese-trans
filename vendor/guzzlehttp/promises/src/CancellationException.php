<?php
/**
 * GuzzleHttp，承诺，取消异常
 */

declare(strict_types=1);

namespace GuzzleHttp\Promise;

/**
 * Exception that is set as the reason for a promise that has been cancelled.
 * 异常，该异常被设置为承诺被取消的原因。
 */
class CancellationException extends RejectionException
{
}
