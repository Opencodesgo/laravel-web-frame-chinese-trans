<?php
/**
 * Illuminate，测试，测试组件
 */

namespace Illuminate\Testing;

use Illuminate\Testing\Assert as PHPUnit;
use Illuminate\Testing\Constraints\SeeInOrder;

class TestComponent
{
    /**
     * The original component.
	 * 原始组件
     *
     * @var \Illuminate\View\Component
     */
    public $component;

    /**
     * The rendered component contents.
	 * 呈现的组件内容
     *
     * @var string
     */
    protected $rendered;

    /**
     * Create a new test component instance.
	 * 创建新的测试组件实例
     *
     * @param  \Illuminate\View\Component  $component
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function __construct($component, $view)
    {
        $this->component = $component;

        $this->rendered = $view->render();
    }

    /**
     * Assert that the given string is contained within the rendered component.
	 * 断言给定的字符串包含在呈现的组件中
     *
     * @param  string  $value
     * @param  bool  $escape
     * @return $this
     */
    public function assertSee($value, $escape = true)
    {
        $value = $escape ? e($value) : $value;

        PHPUnit::assertStringContainsString((string) $value, $this->rendered);

        return $this;
    }

    /**
     * Assert that the given strings are contained in order within the rendered component.
	 * 断言给定的字符串按顺序包含在呈现的组件中
     *
     * @param  array  $values
     * @param  bool  $escape
     * @return $this
     */
    public function assertSeeInOrder(array $values, $escape = true)
    {
        $values = $escape ? array_map('e', $values) : $values;

        PHPUnit::assertThat($values, new SeeInOrder($this->rendered));

        return $this;
    }

    /**
     * Assert that the given string is contained within the rendered component text.
	 * 断言给定的字符串包含在呈现的组件文本中
     *
     * @param  string  $value
     * @param  bool  $escape
     * @return $this
     */
    public function assertSeeText($value, $escape = true)
    {
        $value = $escape ? e($value) : $value;

        PHPUnit::assertStringContainsString((string) $value, strip_tags($this->rendered));

        return $this;
    }

    /**
     * Assert that the given strings are contained in order within the rendered component text.
	 * 断言给定的字符串按顺序包含在呈现的组件文本中
     *
     * @param  array  $values
     * @param  bool  $escape
     * @return $this
     */
    public function assertSeeTextInOrder(array $values, $escape = true)
    {
        $values = $escape ? array_map('e', $values) : $values;

        PHPUnit::assertThat($values, new SeeInOrder(strip_tags($this->rendered)));

        return $this;
    }

    /**
     * Assert that the given string is not contained within the rendered component.
	 * 断言给定的字符串不包含在呈现的组件中
     *
     * @param  string  $value
     * @param  bool  $escape
     * @return $this
     */
    public function assertDontSee($value, $escape = true)
    {
        $value = $escape ? e($value) : $value;

        PHPUnit::assertStringNotContainsString((string) $value, $this->rendered);

        return $this;
    }

    /**
     * Assert that the given string is not contained within the rendered component text.
	 * 断言给定的字符串不包含在呈现的组件文本中
     *
     * @param  string  $value
     * @param  bool  $escape
     * @return $this
     */
    public function assertDontSeeText($value, $escape = true)
    {
        $value = $escape ? e($value) : $value;

        PHPUnit::assertStringNotContainsString((string) $value, strip_tags($this->rendered));

        return $this;
    }

    /**
     * Get the string contents of the rendered component.
	 * 获取呈现组件的字符串内容
     *
     * @return string
     */
    public function __toString()
    {
        return $this->rendered;
    }

    /**
     * Dynamically access properties on the underlying component.
	 * 动态访问底层组件上的属性
     *
     * @param  string  $attribute
     * @return mixed
     */
    public function __get($attribute)
    {
        return $this->component->{$attribute};
    }

    /**
     * Dynamically call methods on the underlying component.
	 * 动态调用底层组件上的方法
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->component->{$method}(...$parameters);
    }
}
