<?php
/**
 * Illuminate, 测试, 测试视图
 */

namespace Illuminate\Testing;

use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Testing\Assert as PHPUnit;
use Illuminate\Testing\Constraints\SeeInOrder;
use Illuminate\View\View;

class TestView
{
    use Macroable;

    /**
     * The original view.
	 * 原先的视图
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
     * Assert that the response view has a given piece of bound data.
	 * 断言响应视图具有给定的绑定数据片段
     *
     * @param  string|array  $key
     * @param  mixed  $value
     * @return $this
     */
    public function assertViewHas($key, $value = null)
    {
        if (is_array($key)) {
            return $this->assertViewHasAll($key);
        }

        if (is_null($value)) {
            PHPUnit::assertTrue(Arr::has($this->view->gatherData(), $key));
        } elseif ($value instanceof Closure) {
            PHPUnit::assertTrue($value(Arr::get($this->view->gatherData(), $key)));
        } elseif ($value instanceof Model) {
            PHPUnit::assertTrue($value->is(Arr::get($this->view->gatherData(), $key)));
        } elseif ($value instanceof Collection) {
            $actual = Arr::get($this->view->gatherData(), $key);

            PHPUnit::assertInstanceOf(Collection::class, $actual);
            PHPUnit::assertSameSize($value, $actual);

            $value->each(fn ($item, $index) => PHPUnit::assertTrue($actual->get($index)->is($item)));
        } else {
            PHPUnit::assertEquals($value, Arr::get($this->view->gatherData(), $key));
        }

        return $this;
    }

    /**
     * Assert that the response view has a given list of bound data.
	 * 断言响应视图具有给定的绑定数据列表
     *
     * @param  array  $bindings
     * @return $this
     */
    public function assertViewHasAll(array $bindings)
    {
        foreach ($bindings as $key => $value) {
            if (is_int($key)) {
                $this->assertViewHas($value);
            } else {
                $this->assertViewHas($key, $value);
            }
        }

        return $this;
    }

    /**
     * Assert that the response view is missing a piece of bound data.
	 * 断言响应视图缺少一段绑定数据
     *
     * @param  string  $key
     * @return $this
     */
    public function assertViewMissing($key)
    {
        PHPUnit::assertFalse(Arr::has($this->view->gatherData(), $key));

        return $this;
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
