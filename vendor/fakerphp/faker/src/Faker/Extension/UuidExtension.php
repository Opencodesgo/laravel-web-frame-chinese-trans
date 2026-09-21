<?php
/**
 * Faker，扩展，Uuid 扩展
 */

namespace Faker\Extension;

/**
 * @experimental This interface is experimental and does not fall under our BC promise
 */
interface UuidExtension extends Extension
{
    /**
     * Generate name based md5 UUID (version 3).
	 * 生成基于名称的md5 UUID(版本3)
     *
     * @example '7e57d004-2b97-0e7a-b45f-5387367791cd'
     */
    public function uuid3(): string;
}
