<?php
/**
 * Illuminate，支持，名称空间项解析器
 */

namespace Illuminate\Support;

class NamespacedItemResolver
{
    /**
     * A cache of the parsed items.
	 * 已解析项的缓存
     *
     * @var array
     */
    protected $parsed = [];

    /**
     * Parse a key into namespace, group, and item.
	 * 将键解析为名称空间、组和项。
     *
     * @param  string  $key
     * @return array
     */
    public function parseKey($key)
    {
        // If we've already parsed the given key, we'll return the cached version we
        // already have, as this will save us some processing. We cache off every
        // key we parse so we can quickly return it on all subsequent requests.
		// 如果我们已经解析了给定的键，我们将返回缓存的版本。
        if (isset($this->parsed[$key])) {
            return $this->parsed[$key];
        }

        // If the key does not contain a double colon, it means the key is not in a
        // namespace, and is just a regular configuration item. Namespaces are a
        // tool for organizing configuration items for things such as modules.
		// 如果键不包含双冒号，则表示该键不在。
        if (strpos($key, '::') === false) {
            $segments = explode('.', $key);

            $parsed = $this->parseBasicSegments($segments);
        } else {
            $parsed = $this->parseNamespacedSegments($key);
        }

        // Once we have the parsed array of this key's elements, such as its groups
        // and namespace, we will cache each array inside a simple list that has
        // the key and the parsed array for quick look-ups for later requests.
		// 一旦我们有了这个键的元素的解析数组，比如它在命名空间中，我们将把每个数组缓存到一个简单的列表中。
        return $this->parsed[$key] = $parsed;
    }

    /**
     * Parse an array of basic segments.
	 * 解析基本段数组
     *
     * @param  array  $segments
     * @return array
     */
    protected function parseBasicSegments(array $segments)
    {
        // The first segment in a basic array will always be the group, so we can go
        // ahead and grab that segment. If there is only one total segment we are
        // just pulling an entire group out of the array and not a single item.
		// 基本数组的第一个段总是组，所以我们可以。
        $group = $segments[0];

        // If there is more than one segment in this group, it means we are pulling
        // a specific item out of a group and will need to return this item name
        // as well as the group so we know which item to pull from the arrays.
		// 如果这组里有不止一个片段，那就意味着我们在拉扯。
        $item = count($segments) === 1
                    ? null
                    : implode('.', array_slice($segments, 1));

        return [null, $group, $item];
    }

    /**
     * Parse an array of namespaced segments.
	 * 解析一个命名空间段数组
     *
     * @param  string  $key
     * @return array
     */
    protected function parseNamespacedSegments($key)
    {
        [$namespace, $item] = explode('::', $key);

        // First we'll just explode the first segment to get the namespace and group
        // since the item should be in the remaining segments. Once we have these
        // two pieces of data we can proceed with parsing out the item's value.
		// 首先，我们将爆炸第一个片段，以获得名称空间和组。
        $itemSegments = explode('.', $item);

        $groupAndItem = array_slice(
            $this->parseBasicSegments($itemSegments), 1
        );

        return array_merge([$namespace], $groupAndItem);
    }

    /**
     * Set the parsed value of a key.
	 * 设置键的解析值
     *
     * @param  string  $key
     * @param  array  $parsed
     * @return void
     */
    public function setParsedKey($key, $parsed)
    {
        $this->parsed[$key] = $parsed;
    }
}
