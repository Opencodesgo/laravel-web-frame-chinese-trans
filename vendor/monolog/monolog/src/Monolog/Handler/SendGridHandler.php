<?php declare(strict_types=1);

/**
 * Monolog，处理程序，Send Grid 处理程序
 *

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Handler;

use Monolog\Logger;

/**
 * SendGridrHandler uses the SendGrid API v2 function to send Log emails, more information in https://sendgrid.com/docs/API_Reference/Web_API/mail.html
 * SendGridrHandler使用SendGrid API v2函数发送日志邮件,更多信息。
 *
 * @author Ricardo Fontanelli <ricardo.fontanelli@hotmail.com>
 */
class SendGridHandler extends MailHandler
{
    /**
     * The SendGrid API User
     * @var string
     */
    protected $apiUser;

    /**
     * The SendGrid API Key
     * @var string
     */
    protected $apiKey;

    /**
     * The email addresses to which the message will be sent
	 * 将发送消息的电子邮件地址
     * @var string
     */
    protected $from;

    /**
     * The email addresses to which the message will be sent
	 * 邮件将被发送到的电子邮件地址
     * @var string[]
     */
    protected $to;

    /**
     * The subject of the email
	 * 邮件的主题
     * @var string
     */
    protected $subject;

    /**
     * @param string          $apiUser The SendGrid API User
     * @param string          $apiKey  The SendGrid API Key
     * @param string          $from    The sender of the email
     * @param string|string[] $to      The recipients of the email
     * @param string          $subject The subject of the mail
     */
    public function __construct(string $apiUser, string $apiKey, string $from, $to, string $subject, $level = Logger::ERROR, bool $bubble = true)
    {
        if (!extension_loaded('curl')) {
            throw new MissingExtensionException('The curl extension is needed to use the SendGridHandler');
        }

        parent::__construct($level, $bubble);
        $this->apiUser = $apiUser;
        $this->apiKey = $apiKey;
        $this->from = $from;
        $this->to = (array) $to;
        $this->subject = $subject;
    }

    /**
     * {@inheritDoc}
     */
    protected function send(string $content, array $records): void
    {
        $message = [];
        $message['api_user'] = $this->apiUser;
        $message['api_key'] = $this->apiKey;
        $message['from'] = $this->from;
        foreach ($this->to as $recipient) {
            $message['to[]'] = $recipient;
        }
        $message['subject'] = $this->subject;
        $message['date'] = date('r');

        if ($this->isHtmlBody($content)) {
            $message['html'] = $content;
        } else {
            $message['text'] = $content;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.sendgrid.com/api/mail.send.json');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($message));
        Curl\Util::execute($ch, 2);
    }
}
