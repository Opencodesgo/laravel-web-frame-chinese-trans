<?php
/**
 * Faker，扩展，电话号码扩展
 */

namespace Faker\Extension;

/**
 * @experimental This interface is experimental and does not fall under our BC promise
 * 这个接口是实验性的，不属于我们的BC承诺
 */
interface PhoneNumberExtension extends Extension
{
    /**
     * @example '555-123-546'
     */
    public function phoneNumber(): string;

    /**
     * @example +27113456789
     */
    public function e164PhoneNumber(): string;
}
