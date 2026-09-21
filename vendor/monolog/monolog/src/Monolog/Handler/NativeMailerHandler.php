<?php declare(strict_types=1);

/**
 * Monolog，处理器，本机邮件处理程序
 */

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
use Monolog\Formatter\LineFormatter;

/**
 * NativeMailerHandler uses the mail() function to send the emails
 * nativeemailerhandler使用mail（）函数发送电子邮件
 *
 * @author Christophe Coevoet <stof@notk.org>
 * @author Mark Garrett <mark@moderndeveloperllc.com>
 */
class NativeMailerHandler extends MailHandler
{
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
     * Optional headers for the message
	 * 消息的可选标头
     * @var string[]
     */
    protected $headers = [];

    /**
     * Optional parameters for the message
	 * 消息的可选参数
     * @var string[]
     */
    protected $parameters = [];

    /**
     * The wordwrap length for the message
	 * 消息的换行长度
     * @var int
     */
    protected $maxColumnWidth;

    /**
     * The Content-type for the message
	 * 消息的内容类型
     * @var string|null
     */
    protected $contentType;

    /**
     * The encoding for the message
	 * 消息的编码
     * @var string
     */
    protected $encoding = 'utf-8';

    /**
     * @param string|string[] $to             The receiver of the mail
     * @param string          $subject        The subject of the mail
     * @param string          $from           The sender of the mail
     * @param int             $maxColumnWidth The maximum column width that the message lines will have
     */
    public function __construct($to, string $subject, string $from, $level = Logger::ERROR, bool $bubble = true, int $maxColumnWidth = 70)
    {
        parent::__construct($level, $bubble);
        $this->to = (array) $to;
        $this->subject = $subject;
        $this->addHeader(sprintf('From: %s', $from));
        $this->maxColumnWidth = $maxColumnWidth;
    }

    /**
     * Add headers to the message
	 * 向消息添加标题
     *
     * @param string|string[] $headers Custom added headers
     */
    public function addHeader($headers): self
    {
        foreach ((array) $headers as $header) {
            if (strpos($header, "\n") !== false || strpos($header, "\r") !== false) {
                throw new \InvalidArgumentException('Headers can not contain newline characters for security reasons');
            }
            $this->headers[] = $header;
        }

        return $this;
    }

    /**
     * Add parameters to the message
	 * 向消息添加参数
     *
     * @param string|string[] $parameters Custom added parameters
     */
    public function addParameter($parameters): self
    {
        $this->parameters = array_merge($this->parameters, (array) $parameters);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function send(string $content, array $records): void
    {
        $contentType = $this->getContentType() ?: ($this->isHtmlBody($content) ? 'text/html' : 'text/plain');

        if ($contentType !== 'text/html') {
            $content = wordwrap($content, $this->maxColumnWidth);
        }

        $headers = ltrim(implode("\r\n", $this->headers) . "\r\n", "\r\n");
        $headers .= 'Content-type: ' . $contentType . '; charset=' . $this->getEncoding() . "\r\n";
        if ($contentType === 'text/html' && false === strpos($headers, 'MIME-Version:')) {
            $headers .= 'MIME-Version: 1.0' . "\r\n";
        }

        $subject = $this->subject;
        if ($records) {
            $subjectFormatter = new LineFormatter($this->subject);
            $subject = $subjectFormatter->format($this->getHighestRecord($records));
        }

        $parameters = implode(' ', $this->parameters);
        foreach ($this->to as $to) {
            mail($to, $subject, $content, $headers, $parameters);
        }
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * @param string $contentType The content type of the email - Defaults to text/plain. Use text/html for HTML messages.
	 * 电子邮件的内容类型-默认为文本/普通。对html消息使用text/html。
     */
    public function setContentType(string $contentType): self
    {
        if (strpos($contentType, "\n") !== false || strpos($contentType, "\r") !== false) {
            throw new \InvalidArgumentException('The content type can not contain newline characters to prevent email header injection');
        }

        $this->contentType = $contentType;

        return $this;
    }

    public function setEncoding(string $encoding): self
    {
        if (strpos($encoding, "\n") !== false || strpos($encoding, "\r") !== false) {
            throw new \InvalidArgumentException('The encoding can not contain newline characters to prevent email header injection');
        }

        $this->encoding = $encoding;

        return $this;
    }
}
