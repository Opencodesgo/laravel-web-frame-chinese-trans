<?php
/**
 * Faker，扩展，生成器意识延伸特性
 */

declare(strict_types=1);

namespace Faker\Extension;

use Faker\Generator;

/**
 * A helper trait to be used with GeneratorAwareExtension.
 * 与GeneratorAwareExtension一起使用的助手特性。
 */
trait GeneratorAwareExtensionTrait
{
    /**
     * @var Generator|null
     */
    private $generator;

    /**
     * @return static
     */
    public function withGenerator(Generator $generator): Extension
    {
        $instance = clone $this;

        $instance->generator = $generator;

        return $instance;
    }
}
