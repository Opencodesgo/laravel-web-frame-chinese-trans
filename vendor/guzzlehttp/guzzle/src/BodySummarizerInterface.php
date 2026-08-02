<?php
/**
 * GuzzleHttp，主体摘要接口
 */

namespace GuzzleHttp;

use Psr\Http\Message\MessageInterface;

interface BodySummarizerInterface
{
    /**
     * Returns a summarized message body.
	 * 返回一个总结消息体
     */
    public function summarize(MessageInterface $message): ?string;
}
