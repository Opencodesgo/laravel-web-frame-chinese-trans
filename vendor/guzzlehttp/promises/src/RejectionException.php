<?php
/**
 * GuzzleHttp，许诺，拒绝异常
 */

declare(strict_types=1);

namespace GuzzleHttp\Promise;

/**
 * A special exception that is thrown when waiting on a rejected promise.
 * 当等待被拒绝的承诺时,会抛出一个特殊的例外。
 *
 * The reason value is available via the getReason() method.
 */
class RejectionException extends \RuntimeException
{
    /** @var mixed Rejection reason. */
    private $reason;

    /**
     * @param mixed       $reason      Rejection reason.
     * @param string|null $description Optional description.
     */
    public function __construct($reason, ?string $description = null)
    {
        $this->reason = $reason;

        $message = 'The promise was rejected';

        if ($description) {
            $message .= ' with reason: '.$description;
        } elseif (is_string($reason)
            || (is_object($reason) && method_exists($reason, '__toString'))
        ) {
            $message .= ' with reason: '.$this->reason;
        } elseif ($reason instanceof \JsonSerializable) {
            $message .= ' with reason: '.json_encode($this->reason, JSON_PRETTY_PRINT);
        }

        parent::__construct($message);
    }

    /**
     * Returns the rejection reason.
	 * 返回拒绝的理由
     *
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }
}
