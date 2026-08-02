<?php
/**
 * Faker，扩展，国家扩展
 */

namespace Faker\Extension;

/**
 * @experimental This interface is experimental and does not fall under our BC promise
 */
interface CountryExtension extends Extension
{
    /**
     * @example 'Japan'
     */
    public function country(): string;
}
