<?php

/**
 * GuzzleHttp，主体缩写器接口
 */

namespace GuzzleHttp;

use Psr\Http\Message\MessageInterface;

interface BodySummarizerInterface
{
    /**
     * Returns a summarized message body.
	 * 返回摘要消息正文
     */
    public function summarize(MessageInterface $message): ?string;
}
