<?php
/**
 * Illuminate，测试，测试视图
 */

namespace Illuminate\Testing;

use Illuminate\Support\Traits\Macroable;
use Illuminate\Testing\Assert as PHPUnit;
use Illuminate\Testing\Constraints\SeeInOrder;
use Illuminate\View\View;

class TestView
{
    use Macroable;

    /**
     * The original view.
	 * 原始视图
     *
     * @var \Illuminate\View\View
     */
    protected $view;

    /**
     * The rendered view contents.
	 * 呈现的视图内容
     *
     * @var string
     */
    protected $rendered;

    /**
     * Create a new test view instance.
	 * 创建一个新的测试视图实例
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function __construct(View $view)
    {
        $this->view = $view;
        $this->rendered = $view->render();
    }

    /**
     * Assert that the given string is contained within the view.
	 * 断言给定的字符串包含在视图中
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
     * Assert that the given strings are contained in order within the view.
	 * 断言给定的字符串按顺序包含在视图中
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
     * Assert that the given string is contained within the view text.
	 * 断言给定的字符串包含在视图文本中
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
     * Assert that the given strings are contained in order within the view text.
	 * 断言给定的字符串按顺序包含在视图文本中
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
     * Assert that the given string is not contained within the view.
	 * 断言给定的字符串不包含在视图中
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
     * Assert that the given string is not contained within the view text.
	 * 断言给定的字符串不包含在视图文本中
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
     * Get the string contents of the rendered view.
	 * 获取呈现视图的字符串内容
     *
     * @return string
     */
    public function __toString()
    {
        return $this->rendered;
    }
}
