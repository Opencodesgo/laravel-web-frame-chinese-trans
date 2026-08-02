<?php
/**
 * Illuminate，测试，可断言 Json字符串
 */

namespace Illuminate\Testing;

use ArrayAccess;
use Countable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Testing\Assert as PHPUnit;
use JsonSerializable;

class AssertableJsonString implements ArrayAccess, Countable
{
    /**
     * The original encoded json.
	 * 原始编码json
     *
     * @var \Illuminate\Contracts\Support\Jsonable|\JsonSerializable|array
     */
    public $json;

    /**
     * The decoded json contents.
	 * 解码后的json内容
     *
     * @var array|null
     */
    protected $decoded;

    /**
     * Create a new assertable JSON string instance.
	 * 创建一个新的可断言JSON字符串实例
     *
     * @param  \Illuminate\Contracts\Support\Jsonable|\JsonSerializable|array|string  $jsonable
     * @return void
     */
    public function __construct($jsonable)
    {
        $this->json = $jsonable;

        if ($jsonable instanceof JsonSerializable) {
            $this->decoded = $jsonable->jsonSerialize();
        } elseif ($jsonable instanceof Jsonable) {
            $this->decoded = json_decode($jsonable->toJson(), true);
        } elseif (is_array($jsonable)) {
            $this->decoded = $jsonable;
        } else {
            $this->decoded = json_decode($jsonable, true);
        }
    }

    /**
     * Validate and return the decoded response JSON.
	 * 验证并返回解码后的响应JSON
     *
     * @param  string|null  $key
     * @return mixed
     */
    public function json($key = null)
    {
        return data_get($this->decoded, $key);
    }

    /**
     * Assert that the response JSON has the expected count of items at the given key.
	 * 断言响应JSON具有给定键处的预期项计数
     *
     * @param  int  $count
     * @param  string|null  $key
     * @return $this
     */
    public function assertCount(int $count, $key = null)
    {
        if (! is_null($key)) {
            PHPUnit::assertCount(
                $count, data_get($this->decoded, $key),
                "Failed to assert that the response count matched the expected {$count}"
            );

            return $this;
        }

        PHPUnit::assertCount($count,
            $this->decoded,
            "Failed to assert that the response count matched the expected {$count}"
        );

        return $this;
    }

    /**
     * Assert that the response has the exact given JSON.
	 * 断言响应具有确切给定的JSON
     *
     * @param  array  $data
     * @return $this
     */
    public function assertExact(array $data)
    {
        $actual = $this->reorderAssocKeys((array) $this->decoded);

        $expected = $this->reorderAssocKeys($data);

        PHPUnit::assertEquals(
            json_encode($expected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            json_encode($actual, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        return $this;
    }

    /**
     * Assert that the response has the similar JSON as given.
	 * 断言响应具有与给定的类似的JSON
     *
     * @param  array  $data
     * @return $this
     */
    public function assertSimilar(array $data)
    {
        $actual = json_encode(Arr::sortRecursive(
            (array) $this->decoded
        ));

        PHPUnit::assertEquals(json_encode(Arr::sortRecursive($data)), $actual);

        return $this;
    }

    /**
     * Assert that the response contains the given JSON fragment.
	 * 断言响应包含给定的JSON片段
     *
     * @param  array  $data
     * @return $this
     */
    public function assertFragment(array $data)
    {
        $actual = json_encode(Arr::sortRecursive(
            (array) $this->decoded
        ));

        foreach (Arr::sortRecursive($data) as $key => $value) {
            $expected = $this->jsonSearchStrings($key, $value);

            PHPUnit::assertTrue(
                Str::contains($actual, $expected),
                'Unable to find JSON fragment: '.PHP_EOL.PHP_EOL.
                '['.json_encode([$key => $value]).']'.PHP_EOL.PHP_EOL.
                'within'.PHP_EOL.PHP_EOL.
                "[{$actual}]."
            );
        }

        return $this;
    }

    /**
     * Assert that the response does not contain the given JSON fragment.
	 * 断言响应不包含给定的JSON片段
     *
     * @param  array  $data
     * @param  bool  $exact
     * @return $this
     */
    public function assertMissing(array $data, $exact = false)
    {
        if ($exact) {
            return $this->assertMissingExact($data);
        }

        $actual = json_encode(Arr::sortRecursive(
            (array) $this->decoded
        ));

        foreach (Arr::sortRecursive($data) as $key => $value) {
            $unexpected = $this->jsonSearchStrings($key, $value);

            PHPUnit::assertFalse(
                Str::contains($actual, $unexpected),
                'Found unexpected JSON fragment: '.PHP_EOL.PHP_EOL.
                '['.json_encode([$key => $value]).']'.PHP_EOL.PHP_EOL.
                'within'.PHP_EOL.PHP_EOL.
                "[{$actual}]."
            );
        }

        return $this;
    }

    /**
     * Assert that the response does not contain the exact JSON fragment.
	 * 断言响应不包含确切的JSON片段
     *
     * @param  array  $data
     * @return $this
     */
    public function assertMissingExact(array $data)
    {
        $actual = json_encode(Arr::sortRecursive(
            (array) $this->decoded
        ));

        foreach (Arr::sortRecursive($data) as $key => $value) {
            $unexpected = $this->jsonSearchStrings($key, $value);

            if (! Str::contains($actual, $unexpected)) {
                return $this;
            }
        }

        PHPUnit::fail(
            'Found unexpected JSON fragment: '.PHP_EOL.PHP_EOL.
            '['.json_encode($data).']'.PHP_EOL.PHP_EOL.
            'within'.PHP_EOL.PHP_EOL.
            "[{$actual}]."
        );

        return $this;
    }

    /**
     * Assert that the expected value and type exists at the given path in the response.
	 * 断言期望的值和类型存在于响应中的给定路径中
     *
     * @param  string  $path
     * @param  mixed  $expect
     * @return $this
     */
    public function assertPath($path, $expect)
    {
        PHPUnit::assertSame($expect, $this->json($path));

        return $this;
    }

    /**
     * Assert that the response has a given JSON structure.
	 * 断言响应具有给定的JSON结构
     *
     * @param  array|null  $structure
     * @param  array|null  $responseData
     * @return $this
     */
    public function assertStructure(array $structure = null, $responseData = null)
    {
        if (is_null($structure)) {
            return $this->assertSimilar($this->decoded);
        }

        if (! is_null($responseData)) {
            return (new static($responseData))->assertStructure($structure);
        }

        foreach ($structure as $key => $value) {
            if (is_array($value) && $key === '*') {
                PHPUnit::assertIsArray($this->decoded);

                foreach ($this->decoded as $responseDataItem) {
                    $this->assertStructure($structure['*'], $responseDataItem);
                }
            } elseif (is_array($value)) {
                PHPUnit::assertArrayHasKey($key, $this->decoded);

                $this->assertStructure($structure[$key], $this->decoded[$key]);
            } else {
                PHPUnit::assertArrayHasKey($value, $this->decoded);
            }
        }

        return $this;
    }

    /**
     * Assert that the response is a superset of the given JSON.
	 * 断言响应是给定JSON的超集
     *
     * @param  array  $data
     * @param  bool  $strict
     * @return $this
     */
    public function assertSubset(array $data, $strict = false)
    {
        PHPUnit::assertArraySubset(
            $data, $this->decoded, $strict, $this->assertJsonMessage($data)
        );

        return $this;
    }

    /**
     * Reorder associative array keys to make it easy to compare arrays.
	 * 重新排序关联数组键，以便于比较数组。
     *
     * @param  array  $data
     * @return array
     */
    protected function reorderAssocKeys(array $data)
    {
        $data = Arr::dot($data);
        ksort($data);

        $result = [];

        foreach ($data as $key => $value) {
            Arr::set($result, $key, $value);
        }

        return $result;
    }

    /**
     * Get the assertion message for assertJson.
	 * 获取assertJson的断言消息
     *
     * @param  array  $data
     * @return string
     */
    protected function assertJsonMessage(array $data)
    {
        $expected = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $actual = json_encode($this->decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return 'Unable to find JSON: '.PHP_EOL.PHP_EOL.
            "[{$expected}]".PHP_EOL.PHP_EOL.
            'within response JSON:'.PHP_EOL.PHP_EOL.
            "[{$actual}].".PHP_EOL.PHP_EOL;
    }

    /**
     * Get the strings we need to search for when examining the JSON.
	 * 获取我们在检查JSON时需要搜索的字符串
     *
     * @param  string  $key
     * @param  string  $value
     * @return array
     */
    protected function jsonSearchStrings($key, $value)
    {
        $needle = substr(json_encode([$key => $value]), 1, -1);

        return [
            $needle.']',
            $needle.'}',
            $needle.',',
        ];
    }

    /**
     * Get the total number of items in the underlying JSON array.
	 * 获取底层JSON数组中的项目总数
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->decoded);
    }

    /**
     * Determine whether an offset exists.
	 * 确定是否存在偏移量
     *
     * @param  mixed  $offset
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->decoded[$offset]);
    }

    /**
     * Get the value at the given offset.
	 * 获取给定偏移量处的值
     *
     * @param  string  $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->decoded[$offset];
    }

    /**
     * Set the value at the given offset.
	 * 在给定的偏移量处设置值
     *
     * @param  string  $offset
     * @param  mixed  $value
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->decoded[$offset] = $value;
    }

    /**
     * Unset the value at the given offset.
	 * 在给定偏移量处取消值的设置
     *
     * @param  string  $offset
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->decoded[$offset]);
    }
}
