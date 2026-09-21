<?php
/**
 * GuzzleHttp，主体缩写器
 */

namespace GuzzleHttp;

use Psr\Http\Message\MessageInterface;

final class BodySummarizer implements BodySummarizerInterface
{
    /**
     * @var int|null
     */
    private $truncateAt;

    public function __construct(?int $truncateAt = null)
    {
        $this->truncateAt = $truncateAt;
    }

    /**
     * Returns a summarized message body.
	 * 返回摘要消息正文
     */
    public function summarize(MessageInterface $message): ?string
    {
        return $this->truncateAt === null
            ? Psr7\Message::bodySummary($message)
            : Psr7\Message::bodySummary($message, $this->truncateAt);
    }
}
