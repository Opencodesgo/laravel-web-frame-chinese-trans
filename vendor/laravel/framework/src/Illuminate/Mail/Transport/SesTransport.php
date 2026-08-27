<?php
/**
 * Illuminate，邮件，传输，Ses 传输
 */

namespace Illuminate\Mail\Transport;

use Aws\Exception\AwsException;
use Aws\Ses\SesClient;
use Exception;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Message;

class SesTransport extends AbstractTransport
{
    /**
     * The Amazon SES instance.
	 * Amazon SES实例
     *
     * @var \Aws\Ses\SesClient
     */
    protected $ses;

    /**
     * The Amazon SES transmission options.
	 * 亚马逊SES传输选项
     *
     * @var array
     */
    protected $options = [];

    /**
     * Create a new SES transport instance.
	 * 创建一个新的SES传输实例
     *
     * @param  \Aws\Ses\SesClient  $ses
     * @param  array  $options
     * @return void
     */
    public function __construct(SesClient $ses, $options = [])
    {
        $this->ses = $ses;
        $this->options = $options;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function doSend(SentMessage $message): void
    {
        $options = $this->options;

        if ($message->getOriginalMessage() instanceof Message) {
            foreach ($message->getOriginalMessage()->getHeaders()->all() as $header) {
                if ($header instanceof MetadataHeader) {
                    $options['Tags'][] = ['Name' => $header->getKey(), 'Value' => $header->getValue()];
                }
            }
        }

        try {
            $result = $this->ses->sendRawEmail(
                array_merge(
                    $options, [
                        'Source' => $message->getEnvelope()->getSender()->toString(),
                        'Destinations' => collect($message->getEnvelope()->getRecipients())
                                ->map
                                ->toString()
                                ->values()
                                ->all(),
                        'RawMessage' => [
                            'Data' => $message->toString(),
                        ],
                    ]
                )
            );
        } catch (AwsException $e) {
            $reason = $e->getAwsErrorMessage() ?? $e->getMessage();

            throw new Exception(
                sprintf('Request to AWS SES API failed. Reason: %s.', $reason),
                is_int($e->getCode()) ? $e->getCode() : 0,
                $e
            );
        }

        $messageId = $result->get('MessageId');

        $message->getOriginalMessage()->getHeaders()->addHeader('X-Message-ID', $messageId);
        $message->getOriginalMessage()->getHeaders()->addHeader('X-SES-Message-ID', $messageId);
    }

    /**
     * Get the Amazon SES client for the SesTransport instance.
	 * 获取SesTransport实例的Amazon SES客户端
     *
     * @return \Aws\Ses\SesClient
     */
    public function ses()
    {
        return $this->ses;
    }

    /**
     * Get the transmission options being used by the transport.
	 * 获取传输所使用的传输选项
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set the transmission options being used by the transport.
	 * 设置传输所使用的传输选项
     *
     * @param  array  $options
     * @return array
     */
    public function setOptions(array $options)
    {
        return $this->options = $options;
    }

    /**
     * Get the string representation of the transport.
	 * 获取传输的字符串表示形式
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'ses';
    }
}
