<?php
/**
 * Illuminate，测试，流畅的，问题，交互
 */

namespace Illuminate\Testing\Fluent\Concerns;

use Illuminate\Support\Str;
use PHPUnit\Framework\Assert as PHPUnit;

trait Interaction
{
    /**
     * The list of interacted properties.
	 * 交互属性列表
     *
     * @var array
     */
    protected $interacted = [];

    /**
     * Marks the property as interacted.
	 * 将属性标记为交互
     *
     * @param  string  $key
     * @return void
     */
    protected function interactsWith(string $key): void
    {
        $prop = Str::before($key, '.');

        if (! in_array($prop, $this->interacted, true)) {
            $this->interacted[] = $prop;
        }
    }

    /**
     * Asserts that all properties have been interacted with.
	 * 断言已与所有属性进行了交互
     *
     * @return void
     */
    public function interacted(): void
    {
        PHPUnit::assertSame(
            [],
            array_diff(array_keys($this->prop()), $this->interacted),
            $this->path
                ? sprintf('Unexpected properties were found in scope [%s].', $this->path)
                : 'Unexpected properties were found on the root level.'
        );
    }

    /**
     * Disables the interaction check.
	 * 禁用交互检查
     *
     * @return $this
     */
    public function etc(): self
    {
        $this->interacted = array_keys($this->prop());

        return $this;
    }

    /**
     * Retrieve a prop within the current scope using "dot" notation.
	 * 使用"点"表示法检索当前作用域中的道具
     *
     * @param  string|null  $key
     * @return mixed
     */
    abstract protected function prop(string $key = null);
}
