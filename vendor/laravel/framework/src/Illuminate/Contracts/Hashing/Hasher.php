<?php
/**
 * Illuminate，契约，哈希，哈希计算器
 */

namespace Illuminate\Contracts\Hashing;

interface Hasher
{
    /**
     * Get information about the given hashed value.
	 * 获取有关给定散列值的信息
     *
     * @param  string  $hashedValue
     * @return array
     */
    public function info($hashedValue);

    /**
     * Hash the given value.
	 * 哈希给定值
     *
     * @param  string  $value
     * @param  array  $options
     * @return string
     */
    public function make($value, array $options = []);

    /**
     * Check the given plain value against a hash.
	 * 根据散列检查给定的普通值
     *
     * @param  string  $value
     * @param  string  $hashedValue
     * @param  array  $options
     * @return bool
     */
    public function check($value, $hashedValue, array $options = []);

    /**
     * Check if the given hash has been hashed using the given options.
	 * 检查给定的散列是否已经使用给定的选项进行了散列
     *
     * @param  string  $hashedValue
     * @param  array  $options
     * @return bool
     */
    public function needsRehash($hashedValue, array $options = []);
}
