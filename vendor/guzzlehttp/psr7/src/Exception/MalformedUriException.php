<?php
/**
 * GuzzleHttp，Psr7，异常，畸形的 Uri异常
 */

declare(strict_types=1);

namespace GuzzleHttp\Psr7\Exception;

use InvalidArgumentException;

/**
 * Exception thrown if a URI cannot be parsed because it's malformed.
 * 如果一个URI不能被解析,因为它是畸形的。
 */
class MalformedUriException extends InvalidArgumentException
{
}
