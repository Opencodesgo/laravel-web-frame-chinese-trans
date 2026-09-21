<?php
/**
 * Illuminate，缓存，存储库
 */

namespace Illuminate\Cache;

use ArrayAccess;
use BadMethodCallException;
use Closure;
use DateTimeInterface;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Carbon;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Support\Traits\Macroable;

/**
 * @mixin \Illuminate\Contracts\Cache\Store
 */
class Repository implements ArrayAccess, CacheContract
{
    use InteractsWithTime;
    use Macroable {
        __call as macroCall;
    }

    /**
     * The cache store implementation.
	 * 缓存存储实现
     *
     * @var \Illuminate\Contracts\Cache\Store
     */
    protected $store;

    /**
     * The event dispatcher implementation.
	 * 事件分派器实现
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * The default number of seconds to store items.
	 * 存储项的默认秒数
     *
     * @var int|null
     */
    protected $default = 3600;

    /**
     * Create a new cache repository instance.
	 * 创建一个新的缓存存储库实例
     *
     * @param  \Illuminate\Contracts\Cache\Store  $store
     * @return void
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * Determine if an item exists in the cache.
	 * 确定缓存中是否存在项
     *
     * @param  array|string  $key
     * @return bool
     */
    public function has($key): bool
    {
        return ! is_null($this->get($key));
    }

    /**
     * Determine if an item doesn't exist in the cache.
	 * 确定某个项是否在缓存中不存在
     *
     * @param  string  $key
     * @return bool
     */
    public function missing($key)
    {
        return ! $this->has($key);
    }

    /**
     * Retrieve an item from the cache by key.
	 * 按键从缓存中检索项
     *
     * @template TCacheValue
     *
     * @param  array|string  $key
     * @param  TCacheValue|(\Closure(): TCacheValue)  $default
     * @return (TCacheValue is null ? mixed : TCacheValue)
     */
    public function get($key, $default = null): mixed
    {
        if (is_array($key)) {
            return $this->many($key);
        }

        $value = $this->store->get($this->itemKey($key));

        // If we could not find the cache value, we will fire the missed event and get
        // the default value for this cache value. This default could be a callback
        // so we will execute the value function which will resolve it if needed.
		// 如果我们找不到缓存值，我们将触发错过的事件并获取。
        if (is_null($value)) {
            $this->event(new CacheMissed($key));

            $value = value($default);
        } else {
            $this->event(new CacheHit($key, $value));
        }

        return $value;
    }

    /**
     * Retrieve multiple items from the cache by key.
	 * 按键从缓存中检索多个项
     *
     * Items not found in the cache will have a null value.
     *
     * @param  array  $keys
     * @return array
     */
    public function many(array $keys)
    {
        $values = $this->store->many(collect($keys)->map(function ($value, $key) {
            return is_string($key) ? $key : $value;
        })->values()->all());

        return collect($values)->map(function ($value, $key) use ($keys) {
            return $this->handleManyResult($keys, $key, $value);
        })->all();
    }

    /**
     * {@inheritdoc}
     *
     * @return iterable
     */
    public function getMultiple($keys, $default = null): iterable
    {
        $defaults = [];

        foreach ($keys as $key) {
            $defaults[$key] = $default;
        }

        return $this->many($defaults);
    }

    /**
     * Handle a result for the "many" method.
	 * 处理"many"方法的结果
     *
     * @param  array  $keys
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function handleManyResult($keys, $key, $value)
    {
        // If we could not find the cache value, we will fire the missed event and get
        // the default value for this cache value. This default could be a callback
        // so we will execute the value function which will resolve it if needed.
        if (is_null($value)) {
            $this->event(new CacheMissed($key));

            return isset($keys[$key]) ? value($keys[$key]) : null;
        }

        // If we found a valid value we will fire the "hit" event and return the value
        // back from this function. The "hit" event gives developers an opportunity
        // to listen for every possible cache "hit" throughout this applications.
        $this->event(new CacheHit($key, $value));

        return $value;
    }

    /**
     * Retrieve an item from the cache and delete it.
	 * 从缓存中检索项并删除它
     *
     * @template TCacheValue
     *
     * @param  array|string  $key
     * @param  TCacheValue|(\Closure(): TCacheValue)  $default
     * @return (TCacheValue is null ? mixed : TCacheValue)
     */
    public function pull($key, $default = null)
    {
        return tap($this->get($key, $default), function () use ($key) {
            $this->forget($key);
        });
    }

    /**
     * Store an item in the cache.
	 * 在缓存中存储项
     *
     * @param  array|string  $key
     * @param  mixed  $value
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl
     * @return bool
     */
    public function put($key, $value, $ttl = null)
    {
        if (is_array($key)) {
            return $this->putMany($key, $value);
        }

        if ($ttl === null) {
            return $this->forever($key, $value);
        }

        $seconds = $this->getSeconds($ttl);

        if ($seconds <= 0) {
            return $this->forget($key);
        }

        $result = $this->store->put($this->itemKey($key), $value, $seconds);

        if ($result) {
            $this->event(new KeyWritten($key, $value, $seconds));
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function set($key, $value, $ttl = null): bool
    {
        return $this->put($key, $value, $ttl);
    }

    /**
     * Store multiple items in the cache for a given number of seconds.
	 * 在给定的秒数内将多个项存储在缓存中
     *
     * @param  array  $values
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl
     * @return bool
     */
    public function putMany(array $values, $ttl = null)
    {
        if ($ttl === null) {
            return $this->putManyForever($values);
        }

        $seconds = $this->getSeconds($ttl);

        if ($seconds <= 0) {
            return $this->deleteMultiple(array_keys($values));
        }

        $result = $this->store->putMany($values, $seconds);

        if ($result) {
            foreach ($values as $key => $value) {
                $this->event(new KeyWritten($key, $value, $seconds));
            }
        }

        return $result;
    }

    /**
     * Store multiple items in the cache indefinitely.
	 * 无限期地在缓存中存储多个项
     *
     * @param  array  $values
     * @return bool
     */
    protected function putManyForever(array $values)
    {
        $result = true;

        foreach ($values as $key => $value) {
            if (! $this->forever($key, $value)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function setMultiple($values, $ttl = null): bool
    {
        return $this->putMany(is_array($values) ? $values : iterator_to_array($values), $ttl);
    }

    /**
     * Store an item in the cache if the key does not exist.
	 * 如果键不存在，则将项存储在缓存中。
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl
     * @return bool
     */
    public function add($key, $value, $ttl = null)
    {
        $seconds = null;

        if ($ttl !== null) {
            $seconds = $this->getSeconds($ttl);

            if ($seconds <= 0) {
                return false;
            }

            // If the store has an "add" method we will call the method on the store so it
            // has a chance to override this logic. Some drivers better support the way
            // this operation should work with a total "atomic" implementation of it.
            if (method_exists($this->store, 'add')) {
                return $this->store->add(
                    $this->itemKey($key), $value, $seconds
                );
            }
        }

        // If the value did not exist in the cache, we will put the value in the cache
        // so it exists for subsequent requests. Then, we will return true so it is
        // easy to know if the value gets added. Otherwise, we will return false.
        if (is_null($this->get($key))) {
            return $this->put($key, $value, $seconds);
        }

        return false;
    }

    /**
     * Increment the value of an item in the cache.
	 * 增加缓存中项的值
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return int|bool
     */
    public function increment($key, $value = 1)
    {
        return $this->store->increment($key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
	 * 递减缓存中项的值
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return int|bool
     */
    public function decrement($key, $value = 1)
    {
        return $this->store->decrement($key, $value);
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
        $result = $this->store->forever($this->itemKey($key), $value);

        if ($result) {
            $this->event(new KeyWritten($key, $value));
        }

        return $result;
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @template TCacheValue
     *
     * @param  string  $key
     * @param  \Closure|\DateTimeInterface|\DateInterval|int|null  $ttl
     * @param  \Closure(): TCacheValue  $callback
     * @return TCacheValue
     */
    public function remember($key, $ttl, Closure $callback)
    {
        $value = $this->get($key);

        // If the item exists in the cache we will just return this immediately and if
        // not we will execute the given Closure and cache the result of that for a
        // given number of seconds so it's available for all subsequent requests.
        if (! is_null($value)) {
            return $value;
        }

        $value = $callback();

        $this->put($key, $value, value($ttl, $value));

        return $value;
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result forever.
     *
     * @template TCacheValue
     *
     * @param  string  $key
     * @param  \Closure(): TCacheValue  $callback
     * @return TCacheValue
     */
    public function sear($key, Closure $callback)
    {
        return $this->rememberForever($key, $callback);
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result forever.
     *
     * @template TCacheValue
     *
     * @param  string  $key
     * @param  \Closure(): TCacheValue  $callback
     * @return TCacheValue
     */
    public function rememberForever($key, Closure $callback)
    {
        $value = $this->get($key);

        // If the item exists in the cache we will just return this immediately
        // and if not we will execute the given Closure and cache the result
        // of that forever so it is available for all subsequent requests.
        if (! is_null($value)) {
            return $value;
        }

        $this->forever($key, $value = $callback());

        return $value;
    }

    /**
     * Remove an item from the cache.
	 * 从缓存中删除项
     *
     * @param  string  $key
     * @return bool
     */
    public function forget($key)
    {
        return tap($this->store->forget($this->itemKey($key)), function ($result) use ($key) {
            if ($result) {
                $this->event(new KeyForgotten($key));
            }
        });
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function delete($key): bool
    {
        return $this->forget($key);
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function deleteMultiple($keys): bool
    {
        $result = true;

        foreach ($keys as $key) {
            if (! $this->forget($key)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function clear(): bool
    {
        return $this->store->flush();
    }

    /**
     * Begin executing a new tags operation if the store supports it.
	 * 如果存储支持，开始执行新的标记操作。
     *
     * @param  array|mixed  $names
     * @return \Illuminate\Cache\TaggedCache
     *
     * @throws \BadMethodCallException
     */
    public function tags($names)
    {
        if (! $this->supportsTags()) {
            throw new BadMethodCallException('This cache store does not support tagging.');
        }

        $cache = $this->store->tags(is_array($names) ? $names : func_get_args());

        if (! is_null($this->events)) {
            $cache->setEventDispatcher($this->events);
        }

        return $cache->setDefaultCacheTime($this->default);
    }

    /**
     * Format the key for a cache item.
	 * 格式化缓存项的键
     *
     * @param  string  $key
     * @return string
     */
    protected function itemKey($key)
    {
        return $key;
    }

    /**
     * Calculate the number of seconds for the given TTL.
	 * 计算给定TTL的秒数
     *
     * @param  \DateTimeInterface|\DateInterval|int  $ttl
     * @return int
     */
    protected function getSeconds($ttl)
    {
        $duration = $this->parseDateInterval($ttl);

        if ($duration instanceof DateTimeInterface) {
            $duration = Carbon::now()->diffInRealSeconds($duration, false);
        }

        return (int) ($duration > 0 ? $duration : 0);
    }

    /**
     * Determine if the current store supports tags.
	 * 确定当前存储是否支持标记
     *
     * @return bool
     */
    public function supportsTags()
    {
        return method_exists($this->store, 'tags');
    }

    /**
     * Get the default cache time.
	 * 获取默认缓存时间
     *
     * @return int|null
     */
    public function getDefaultCacheTime()
    {
        return $this->default;
    }

    /**
     * Set the default cache time in seconds.
	 * 设置默认缓存时间（以秒为单位）
     *
     * @param  int|null  $seconds
     * @return $this
     */
    public function setDefaultCacheTime($seconds)
    {
        $this->default = $seconds;

        return $this;
    }

    /**
     * Get the cache store implementation.
	 * 获取缓存存储实现
     *
     * @return \Illuminate\Contracts\Cache\Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * Fire an event for this cache instance.
	 * 触发此缓存实例的事件
     *
     * @param  object|string  $event
     * @return void
     */
    protected function event($event)
    {
        $this->events?->dispatch($event);
    }

    /**
     * Get the event dispatcher instance.
	 * 获取事件调度程序实例
     *
     * @return \Illuminate\Contracts\Events\Dispatcher
     */
    public function getEventDispatcher()
    {
        return $this->events;
    }

    /**
     * Set the event dispatcher instance.
	 * 设置事件调度程序实例
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function setEventDispatcher(Dispatcher $events)
    {
        $this->events = $events;
    }

    /**
     * Determine if a cached value exists.
	 * 确定是否存在缓存值
     *
     * @param  string  $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    /**
     * Retrieve an item from the cache by key.
	 * 按键从缓存中检索项
     *
     * @param  string  $key
     * @return mixed
     */
    public function offsetGet($key): mixed
    {
        return $this->get($key);
    }

    /**
     * Store an item in the cache for the default time.
	 * 在默认时间的缓存中存储项
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value): void
    {
        $this->put($key, $value, $this->default);
    }

    /**
     * Remove an item from the cache.
	 * 从缓存中删除项
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key): void
    {
        $this->forget($key);
    }

    /**
     * Handle dynamic calls into macros or pass missing methods to the store.
	 * 处理对宏的动态调用或将缺少的方法传递给存储库
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return $this->store->$method(...$parameters);
    }

    /**
     * Clone cache repository instance.
	 * 克隆缓存存储库实例
     *
     * @return void
     */
    public function __clone()
    {
        $this->store = clone $this->store;
    }
}
