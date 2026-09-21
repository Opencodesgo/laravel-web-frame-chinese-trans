<?php
/**
 * Symfony，Component，Mime，标题，邮箱列表标头
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mime\Header;

use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Exception\RfcComplianceException;

/**
 * A Mailbox list MIME Header for something like From, To, Cc, and Bcc (one or more named addresses).
 * 邮箱列表MIME头，用于发送、发送、抄送和密件抄送（一个或多个指定地址）。
 *
 * @author Chris Corbyn
 */
final class MailboxListHeader extends AbstractHeader
{
    private array $addresses = [];

    /**
     * @param Address[] $addresses
     */
    public function __construct(string $name, array $addresses)
    {
        parent::__construct($name);

        $this->setAddresses($addresses);
    }

    /**
     * @param Address[] $body
     *
     * @throws RfcComplianceException
     */
    public function setBody(mixed $body): void
    {
        $this->setAddresses($body);
    }

    /**
     * @return Address[]
     *
     * @throws RfcComplianceException
     */
    public function getBody(): array
    {
        return $this->getAddresses();
    }

    /**
     * Sets a list of addresses to be shown in this Header.
	 * 设置要在此报头中显示的地址列表
     *
     * @param Address[] $addresses
     *
     * @throws RfcComplianceException
     */
    public function setAddresses(array $addresses): void
    {
        $this->addresses = [];
        $this->addAddresses($addresses);
    }

    /**
     * Sets a list of addresses to be shown in this Header.
	 * 设置要在此报头中显示的地址列表
     *
     * @param Address[] $addresses
     *
     * @throws RfcComplianceException
     */
    public function addAddresses(array $addresses): void
    {
        foreach ($addresses as $address) {
            $this->addAddress($address);
        }
    }

    /**
     * @throws RfcComplianceException
     */
    public function addAddress(Address $address): void
    {
        $this->addresses[] = $address;
    }

    /**
     * @return Address[]
     */
    public function getAddresses(): array
    {
        return $this->addresses;
    }

    /**
     * Gets the full mailbox list of this Header as an array of valid RFC 2822 strings.
	 * 以有效RFC 2822字符串数组的形式获取此标头的完整邮箱列表。
     *
     * @return string[]
     *
     * @throws RfcComplianceException
     */
    public function getAddressStrings(): array
    {
        $strings = [];
        foreach ($this->addresses as $address) {
            $str = $address->getEncodedAddress();
            if ($name = $address->getName()) {
                $str = $this->createPhrase($this, $name, $this->getCharset(), !$strings).' <'.$str.'>';
            }
            $strings[] = $str;
        }

        return $strings;
    }

    public function getBodyAsString(): string
    {
        return implode(', ', $this->getAddressStrings());
    }

    /**
     * Redefine the encoding requirements for addresses.
	 * 重新定义地址的编码要求
     *
     * All "specials" must be encoded as the full header value will not be quoted
     *
     * @see RFC 2822 3.2.1
     */
    protected function tokenNeedsEncoding(string $token): bool
    {
        return preg_match('/[()<>\[\]:;@\,."]/', $token) || parent::tokenNeedsEncoding($token);
    }
}
