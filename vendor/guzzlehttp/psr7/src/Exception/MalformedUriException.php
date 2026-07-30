<?php
/**
 * GuzzleHttp，Psr7，异常，难看的 Uri异常
 */

declare(strict_types=1);

namespace GuzzleHttp\Psr7\Exception;

use InvalidArgumentException;

/**
 * Exception thrown if a URI cannot be parsed because it's malformed.
 * 如果URI由于格式错误而无法解析，则抛出异常。
 */
class MalformedUriException extends InvalidArgumentException
{
}
