<?php
/**
 * GuzzleHttp，Psr7，无搜索流
 */

declare(strict_types=1);

namespace GuzzleHttp\Psr7;

use Psr\Http\Message\StreamInterface;

/**
 * Stream decorator that prevents a stream from being seeked.
 * 防止查找流的流装饰器
 */
final class NoSeekStream implements StreamInterface
{
    use StreamDecoratorTrait;

    /** @var StreamInterface */
    private $stream;

    public function seek($offset, $whence = SEEK_SET): void
    {
        throw new \RuntimeException('Cannot seek a NoSeekStream');
    }

    public function isSeekable(): bool
    {
        return false;
    }
}
