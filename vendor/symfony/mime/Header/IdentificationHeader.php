<?php
/**
 * Symfony，Component，Mime，标题，识别头
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
 * An ID MIME Header for something like Message-ID or Content-ID (one or more addresses).
 * 一个ID MIME报头，用于消息ID或内容ID（一个或多个地址）。
 *
 * @author Chris Corbyn
 */
final class IdentificationHeader extends AbstractHeader
{
    private array $ids = [];
    private array $idsAsAddresses = [];

    public function __construct(string $name, string|array $ids)
    {
        parent::__construct($name);

        $this->setId($ids);
    }

    /**
     * @param string|string[] $body a string ID or an array of IDs
     *
     * @throws RfcComplianceException
     */
    public function setBody(mixed $body): void
    {
        $this->setId($body);
    }

    public function getBody(): array
    {
        return $this->getIds();
    }

    /**
     * Set the ID used in the value of this header.
	 * 设置该报头值中使用的ID。
     *
     * @param string|string[] $id
     *
     * @throws RfcComplianceException
     */
    public function setId(string|array $id): void
    {
        $this->setIds(\is_array($id) ? $id : [$id]);
    }

    /**
     * Get the ID used in the value of this Header.
	 * 获取此Header值中使用的ID。
     *
     * If multiple IDs are set only the first is returned.
     */
    public function getId(): ?string
    {
        return $this->ids[0] ?? null;
    }

    /**
     * Set a collection of IDs to use in the value of this Header.
	 * 设置一个id集合，在这个Header的值中使用。
     *
     * @param string[] $ids
     *
     * @throws RfcComplianceException
     */
    public function setIds(array $ids): void
    {
        $this->ids = [];
        $this->idsAsAddresses = [];
        foreach ($ids as $id) {
            $this->idsAsAddresses[] = new Address($id);
            $this->ids[] = $id;
        }
    }

    /**
     * Get the list of IDs used in this Header.
	 * 获取此Header中使用的id列表
     *
     * @return string[]
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    public function getBodyAsString(): string
    {
        $addrs = [];
        foreach ($this->idsAsAddresses as $address) {
            $addrs[] = '<'.$address->toString().'>';
        }

        return implode(' ', $addrs);
    }
}
