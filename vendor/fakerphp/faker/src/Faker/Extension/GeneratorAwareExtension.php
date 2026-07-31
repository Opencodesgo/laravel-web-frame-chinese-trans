<?php
/**
 * Faker，扩展，生成器意识扩展
 */

declare(strict_types=1);

namespace Faker\Extension;

use Faker\Generator;

/**
 * @experimental This interface is experimental and does not fall under our BC promise
 */
interface GeneratorAwareExtension extends Extension
{
    /**
     * This method MUST be implemented in such a way as to retain the
     * immutability of the extension, and MUST return an instance that has the
     * new Generator.
	 * 这种方法必须以扩展的不变性的方式实现，必须返回一个具有生成器。
     */
    public function withGenerator(Generator $generator): Extension;
}
