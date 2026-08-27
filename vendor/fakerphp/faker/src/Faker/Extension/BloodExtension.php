<?php
/**
 * Faker，扩展，Blood 扩展
 */

namespace Faker\Extension;

/**
 * @experimental This interface is experimental and does not fall under our BC promise
 */
interface BloodExtension extends Extension
{
    /**
     * Get an actual blood type
	 * 找一个真正的blood
     *
     * @example 'AB'
     */
    public function bloodType(): string;

    /**
     * Get a random resis value
	 * 得到一个随机的阻抗值
     *
     * @example '+'
     */
    public function bloodRh(): string;

    /**
     * Get a full blood group
     *
     * @example 'AB+'
     */
    public function bloodGroup(): string;
}
