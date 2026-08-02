<?php
/**
 * Faker，机会生成器
 */

namespace Faker;

use Faker\Extension\Extension;

/**
 * This generator returns a default value for all called properties
 * and methods. It works with Faker\Generator::optional().
 * 这个生成器返回所有被称为属性和方法的默认值。
 *
 * @mixin Generator
 */
class ChanceGenerator
{
    private $generator;
    private $weight;
    protected $default;

    /**
     * @param Extension|Generator $generator
     */
    public function __construct($generator, float $weight, $default = null)
    {
        $this->default = $default;
        $this->generator = $generator;
        $this->weight = $weight;
    }

    public function ext(string $id)
    {
        return new self($this->generator->ext($id), $this->weight, $this->default);
    }

    /**
     * Catch and proxy all generator calls but return only valid values
	 * 捕获和代理所有生成器调用,但只返回有效值
     *
     * @param string $attribute
     *
     * @deprecated Use a method instead.
     */
    public function __get($attribute)
    {
        trigger_deprecation('fakerphp/faker', '1.14', 'Accessing property "%s" is deprecated, use "%s()" instead.', $attribute, $attribute);

        return $this->__call($attribute, []);
    }

    /**
     * @param string $name
     * @param array  $arguments
     */
    public function __call($name, $arguments)
    {
        if (mt_rand(1, 100) <= (100 * $this->weight)) {
            return call_user_func_array([$this->generator, $name], $arguments);
        }

        return $this->default;
    }
}
