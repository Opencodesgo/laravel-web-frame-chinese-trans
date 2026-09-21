<?php
/**
 * GuzzleHttp，Psr7，丢弃流
 */

declare(strict_types=1);

namespace GuzzleHttp\Psr7;

use Psr\Http\Message\StreamInterface;

/**
 * Stream decorator that begins dropping data once the size of the underlying
 * stream becomes too full.
 * 一旦底层的水流太满就开始删除数据的流装饰器
 */
final class DroppingStream implements StreamInterface
{
    use StreamDecoratorTrait;

    /** @var int */
    private $maxLength;

    /** @var StreamInterface */
    private $stream;

    /**
     * @param StreamInterface $stream    Underlying stream to decorate.
     * @param int             $maxLength Maximum size before dropping data.
     */
    public function __construct(StreamInterface $stream, int $maxLength)
    {
        $this->stream = $stream;
        $this->maxLength = $maxLength;
    }

    public function write($string): int
    {
        $diff = $this->maxLength - $this->stream->getSize();

        // Begin returning 0 when the underlying stream is too large.
        if ($diff <= 0) {
            return 0;
        }

        // Write the stream or a subset of the stream if needed.
		// 如果需要，写入流或流的子集。
        if (strlen($string) < $diff) {
            return $this->stream->write($string);
        }

        return $this->stream->write(substr($string, 0, $diff));
    }
}
