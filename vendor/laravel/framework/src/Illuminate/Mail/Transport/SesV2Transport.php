<?php
/**
 * Illuminate，邮件，传输，SesV2 传输
 */

namespace Illuminate\Mail\Transport;

use Aws\Exception\AwsException;
use Aws\SesV2\SesV2Client;
use Exception;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Message;

class SesV2Transport extends AbstractTransport
{
    /**
     * The Amazon SES V2 instance.
	 * Amazon SES V2 实例
     *
     * @var \Aws\SesV2\SesV2Client
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
     * Create a new SES V2 transport instance.
	 * 创建一个新的SES V2传输实例
     *
     * @param  \Aws\SesV2\SesV2Client  $ses
     * @param  array  $options
     * @return void
     */
    public function __construct(SesV2Client $ses, $options = [])
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
            $result = $this->ses->sendEmail(
                array_merge(
                    $options, [
                        'Source' => $message->getEnvelope()->getSender()->toString(),
                        'Destination' => [
                            'ToAddresses' => collect($message->getEnvelope()->getRecipients())
                                    ->map
                                    ->toString()
                                    ->values()
                                    ->all(),
                        ],
                        'Content' => [
                            'Raw' => [
                                'Data' => $message->toString(),
                            ],
                        ],
                    ]
                )
            );
        } catch (AwsException $e) {
            $reason = $e->getAwsErrorMessage() ?? $e->getMessage();

            throw new Exception(
                sprintf('Request to AWS SES V2 API failed. Reason: %s.', $reason),
                is_int($e->getCode()) ? $e->getCode() : 0,
                $e
            );
        }

        $messageId = $result->get('MessageId');

        $message->getOriginalMessage()->getHeaders()->addHeader('X-Message-ID', $messageId);
        $message->getOriginalMessage()->getHeaders()->addHeader('X-SES-Message-ID', $messageId);
    }

    /**
     * Get the Amazon SES V2 client for the SesV2Transport instance.
	 * 获取SesV2Transport实例的Amazon SES V2客户端
     *
     * @return \Aws\SesV2\SesV2Client
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
        return 'ses-v2';
    }
}
