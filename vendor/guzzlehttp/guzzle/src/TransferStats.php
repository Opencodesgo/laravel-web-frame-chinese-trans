<?php
/**
 * GuzzleHttp，传输数据
 */

namespace GuzzleHttp;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Represents data at the point after it was transferred either successfully
 * or after a network error.
 * 在被转移后,表示数据。
 */
final class TransferStats
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface|null
     */
    private $response;

    /**
     * @var float|null
     */
    private $transferTime;

    /**
     * @var array
     */
    private $handlerStats;

    /**
     * @var mixed|null
     */
    private $handlerErrorData;

    /**
     * @param RequestInterface       $request          Request that was sent.
     * @param ResponseInterface|null $response         Response received (if any)
     * @param float|null             $transferTime     Total handler transfer time.
     * @param mixed                  $handlerErrorData Handler error data.
     * @param array                  $handlerStats     Handler specific stats.
     */
    public function __construct(
        RequestInterface $request,
        ?ResponseInterface $response = null,
        ?float $transferTime = null,
        $handlerErrorData = null,
        array $handlerStats = []
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->transferTime = $transferTime;
        $this->handlerErrorData = $handlerErrorData;
        $this->handlerStats = $handlerStats;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Returns the response that was received (if any).
	 * 返回被接收的响应(如果有的话)
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * Returns true if a response was received.
     */
    public function hasResponse(): bool
    {
        return $this->response !== null;
    }

    /**
     * Gets handler specific error data.
	 * 获取特定于处理程序的错误数据
     *
     * This might be an exception, a integer representing an error code, or
     * anything else. Relying on this value assumes that you know what handler
     * you are using.
     *
     * @return mixed
     */
    public function getHandlerErrorData()
    {
        return $this->handlerErrorData;
    }

    /**
     * Get the effective URI the request was sent to.
	 * 获取请求被发送到的有效URI
     */
    public function getEffectiveUri(): UriInterface
    {
        return $this->request->getUri();
    }

    /**
     * Get the estimated time the request was being transferred by the handler.
	 * 获取处理程序传输请求的估计时间
     *
     * @return float|null Time in seconds.
     */
    public function getTransferTime(): ?float
    {
        return $this->transferTime;
    }

    /**
     * Gets an array of all of the handler specific transfer data.
	 * 获取所有特定于处理程序的传输数据的数组
     */
    public function getHandlerStats(): array
    {
        return $this->handlerStats;
    }

    /**
     * Get a specific handler statistic from the handler by name.
	 * 按名称从处理程序获取特定的处理程序统计信息
     *
     * @param string $stat Handler specific transfer stat to retrieve.
     *
     * @return mixed|null
     */
    public function getHandlerStat(string $stat)
    {
        return $this->handlerStats[$stat] ?? null;
    }
}
