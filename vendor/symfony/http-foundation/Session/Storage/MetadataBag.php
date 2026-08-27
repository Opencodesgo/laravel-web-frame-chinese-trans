<?php
/**
 * Symfony，Component，HttpFoundation，会话，存储，元数据包
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\Session\Storage;

use Symfony\Component\HttpFoundation\Session\SessionBagInterface;

/**
 * Metadata container.
 * 元数据的容器
 *
 * Adds metadata to the session.
 * 向会话添加元数据
 *
 * @author Drak <drak@zikula.org>
 */
class MetadataBag implements SessionBagInterface
{
    public const CREATED = 'c';
    public const UPDATED = 'u';
    public const LIFETIME = 'l';

    private string $name = '__metadata';
    private string $storageKey;

    /**
     * @var array
     */
    protected $meta = [self::CREATED => 0, self::UPDATED => 0, self::LIFETIME => 0];

    /**
     * Unix timestamp.
	 * Unix时间戳
     */
    private int $lastUsed;

    private int $updateThreshold;

    /**
     * @param string $storageKey      The key used to store bag in the session
     * @param int    $updateThreshold The time to wait between two UPDATED updates
     */
    public function __construct(string $storageKey = '_sf2_meta', int $updateThreshold = 0)
    {
        $this->storageKey = $storageKey;
        $this->updateThreshold = $updateThreshold;
    }

    /**
     * @return void
     */
    public function initialize(array &$array)
    {
        $this->meta = &$array;

        if (isset($array[self::CREATED])) {
            $this->lastUsed = $this->meta[self::UPDATED];

            $timeStamp = time();
            if ($timeStamp - $array[self::UPDATED] >= $this->updateThreshold) {
                $this->meta[self::UPDATED] = $timeStamp;
            }
        } else {
            $this->stampCreated();
        }
    }

    /**
     * Gets the lifetime that the session cookie was set with.
	 * 获取会话cookie所设置的生存期
     */
    public function getLifetime(): int
    {
        return $this->meta[self::LIFETIME];
    }

    /**
     * Stamps a new session's metadata.
	 * 戳记新会话的元数据。
     *
     * @param int|null $lifetime Sets the cookie lifetime for the session cookie. A null value
     *                           will leave the system settings unchanged, 0 sets the cookie
     *                           to expire with browser session. Time is in seconds, and is
     *                           not a Unix timestamp.
     *
     * @return void
     */
    public function stampNew(?int $lifetime = null)
    {
        $this->stampCreated($lifetime);
    }

    public function getStorageKey(): string
    {
        return $this->storageKey;
    }

    /**
     * Gets the created timestamp metadata.
	 * 获取创建的时间戳元数据
     *
     * @return int Unix timestamp
     */
    public function getCreated(): int
    {
        return $this->meta[self::CREATED];
    }

    /**
     * Gets the last used metadata.
	 * 获取最后使用的元数据
     *
     * @return int Unix timestamp
     */
    public function getLastUsed(): int
    {
        return $this->lastUsed;
    }

    public function clear(): mixed
    {
        // nothing to do
        return null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets name.
	 * 设置名称
     *
     * @return void
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    private function stampCreated(?int $lifetime = null): void
    {
        $timeStamp = time();
        $this->meta[self::CREATED] = $this->meta[self::UPDATED] = $this->lastUsed = $timeStamp;
        $this->meta[self::LIFETIME] = $lifetime ?? (int) \ini_get('session.cookie_lifetime');
    }
}
