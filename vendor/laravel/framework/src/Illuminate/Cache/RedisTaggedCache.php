<?php
/**
 * Illuminate，缓存，Redis标记缓存
 */

namespace Illuminate\Cache;

class RedisTaggedCache extends TaggedCache
{
    /**
     * Forever reference key.
	 * 永久参考键
     *
     * @var string
     */
    const REFERENCE_KEY_FOREVER = 'forever_ref';
	
    /**
     * Standard reference key.
	 * 标准参考键
     *
     * @var string
     */
    const REFERENCE_KEY_STANDARD = 'standard_ref';

    /**
     * Store an item in the cache.
	 * 在缓存中存储项
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl
     * @return bool
     */
    public function put($key, $value, $ttl = null)
    {
        if ($ttl === null) {
            return $this->forever($key, $value);
        }

        $this->pushStandardKeys($this->tags->getNamespace(), $key);

        return parent::put($key, $value, $ttl);
    }

    /**
     * Increment the value of an item in the cache.
	 * 增加缓存中项的值
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function increment($key, $value = 1)
    {
        $this->pushStandardKeys($this->tags->getNamespace(), $key);

        parent::increment($key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
	 * 递减缓存中项的值
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function decrement($key, $value = 1)
    {
        $this->pushStandardKeys($this->tags->getNamespace(), $key);

        parent::decrement($key, $value);
    }

    /**
     * Store an item in the cache indefinitely.
	 * 将项无限期地存储在缓存中
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return bool
     */
    public function forever($key, $value)
    {
        $this->pushForeverKeys($this->tags->getNamespace(), $key);

        return parent::forever($key, $value);
    }

    /**
     * Remove all items from the cache.
	 * 从缓存中删除所有项
     *
     * @return bool
     */
    public function flush()
    {
        $this->deleteForeverKeys();
        $this->deleteStandardKeys();

        return parent::flush();
    }

    /**
     * Store standard key references into store.
	 * 将标准键引用存储到存储中
     *
     * @param  string  $namespace
     * @param  string  $key
     * @return void
     */
    protected function pushStandardKeys($namespace, $key)
    {
        $this->pushKeys($namespace, $key, self::REFERENCE_KEY_STANDARD);
    }

    /**
     * Store forever key references into store.
	 * 将关键引用永久存储到存储中
     *
     * @param  string  $namespace
     * @param  string  $key
     * @return void
     */
    protected function pushForeverKeys($namespace, $key)
    {
        $this->pushKeys($namespace, $key, self::REFERENCE_KEY_FOREVER);
    }

    /**
     * Store a reference to the cache key against the reference key.
	 * 根据引用键存储对缓存键的引用
     *
     * @param  string  $namespace
     * @param  string  $key
     * @param  string  $reference
     * @return void
     */
    protected function pushKeys($namespace, $key, $reference)
    {
        $fullKey = $this->store->getPrefix().sha1($namespace).':'.$key;

        foreach (explode('|', $namespace) as $segment) {
            $this->store->connection()->sadd($this->referenceKey($segment, $reference), $fullKey);
        }
    }

    /**
     * Delete all of the items that were stored forever.
	 * 删除所有永久存储的项
     *
     * @return void
     */
    protected function deleteForeverKeys()
    {
        $this->deleteKeysByReference(self::REFERENCE_KEY_FOREVER);
    }

    /**
     * Delete all standard items.
	 * 删除所有标准项
     *
     * @return void
     */
    protected function deleteStandardKeys()
    {
        $this->deleteKeysByReference(self::REFERENCE_KEY_STANDARD);
    }

    /**
     * Find and delete all of the items that were stored against a reference.
	 * 查找并删除根据引用存储的所有项
     *
     * @param  string  $reference
     * @return void
     */
    protected function deleteKeysByReference($reference)
    {
        foreach (explode('|', $this->tags->getNamespace()) as $segment) {
            $this->deleteValues($segment = $this->referenceKey($segment, $reference));

            $this->store->connection()->del($segment);
        }
    }

    /**
     * Delete item keys that have been stored against a reference.
	 * 查找并删除根据引用存储的所有项
     *
     * @param  string  $referenceKey
     * @return void
     */
    protected function deleteValues($referenceKey)
    {
        $values = array_unique($this->store->connection()->smembers($referenceKey));

        if (count($values) > 0) {
            foreach (array_chunk($values, 1000) as $valuesChunk) {
                $this->store->connection()->del(...$valuesChunk);
            }
        }
    }

    /**
     * Get the reference key for the segment.
	 * 获取段的参考键
     *
     * @param  string  $segment
     * @param  string  $suffix
     * @return string
     */
    protected function referenceKey($segment, $suffix)
    {
        return $this->store->getPrefix().$segment.':'.$suffix;
    }
}
