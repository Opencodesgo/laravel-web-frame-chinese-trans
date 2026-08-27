<?php
/**
 * Illuminate，视图，问题，管理组件
 */

namespace Illuminate\View\Concerns;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Illuminate\View\ComponentSlot;

trait ManagesComponents
{
    /**
     * The components being rendered.
	 * 正在呈现的组件
     *
     * @var array
     */
    protected $componentStack = [];

    /**
     * The original data passed to the component.
	 * 传递给组件的原始数据
     *
     * @var array
     */
    protected $componentData = [];

    /**
     * The component data for the component that is currently being rendered.
	 * 当前正在呈现的组件的组件数据
     *
     * @var array
     */
    protected $currentComponentData = [];

    /**
     * The slot contents for the component.
	 * 组件的槽位内容
     *
     * @var array
     */
    protected $slots = [];

    /**
     * The names of the slots being rendered.
	 * 正在呈现的插槽的名称
     *
     * @var array
     */
    protected $slotStack = [];

    /**
     * Start a component rendering process.
	 * 启动一个组件呈现过程
     *
     * @param  \Illuminate\Contracts\View\View|\Illuminate\Contracts\Support\Htmlable|\Closure|string  $view
     * @param  array  $data
     * @return void
     */
    public function startComponent($view, array $data = [])
    {
        if (ob_start()) {
            $this->componentStack[] = $view;

            $this->componentData[$this->currentComponent()] = $data;

            $this->slots[$this->currentComponent()] = [];
        }
    }

    /**
     * Get the first view that actually exists from the given list, and start a component.
	 * 从给定列表中获取实际存在的第一个视图，并启动一个组件。
     *
     * @param  array  $names
     * @param  array  $data
     * @return void
     */
    public function startComponentFirst(array $names, array $data = [])
    {
        $name = Arr::first($names, function ($item) {
            return $this->exists($item);
        });

        $this->startComponent($name, $data);
    }

    /**
     * Render the current component.
	 * 渲染当前组件
     *
     * @return string
     */
    public function renderComponent()
    {
        $view = array_pop($this->componentStack);

        $this->currentComponentData = array_merge(
            $previousComponentData = $this->currentComponentData,
            $data = $this->componentData()
        );

        try {
            $view = value($view, $data);

            if ($view instanceof View) {
                return $view->with($data)->render();
            } elseif ($view instanceof Htmlable) {
                return $view->toHtml();
            } else {
                return $this->make($view, $data)->render();
            }
        } finally {
            $this->currentComponentData = $previousComponentData;
        }
    }

    /**
     * Get the data for the given component.
	 * 获取给定组件的数据
     *
     * @return array
     */
    protected function componentData()
    {
        $defaultSlot = new HtmlString(trim(ob_get_clean()));

        $slots = array_merge([
            '__default' => $defaultSlot,
        ], $this->slots[count($this->componentStack)]);

        return array_merge(
            $this->componentData[count($this->componentStack)],
            ['slot' => $defaultSlot],
            $this->slots[count($this->componentStack)],
            ['__laravel_slots' => $slots]
        );
    }

    /**
     * Get an item from the component data that exists above the current component.
	 * 从存在于当前组件上方的组件数据中获取项
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed|null
     */
    public function getConsumableComponentData($key, $default = null)
    {
        if (array_key_exists($key, $this->currentComponentData)) {
            return $this->currentComponentData[$key];
        }

        $currentComponent = count($this->componentStack);

        if ($currentComponent === 0) {
            return value($default);
        }

        for ($i = $currentComponent - 1; $i >= 0; $i--) {
            $data = $this->componentData[$i] ?? [];

            if (array_key_exists($key, $data)) {
                return $data[$key];
            }
        }

        return value($default);
    }

    /**
     * Start the slot rendering process.
	 * 启动槽呈现过程
     *
     * @param  string  $name
     * @param  string|null  $content
     * @param  array  $attributes
     * @return void
     */
    public function slot($name, $content = null, $attributes = [])
    {
        if (func_num_args() === 2 || $content !== null) {
            $this->slots[$this->currentComponent()][$name] = $content;
        } elseif (ob_start()) {
            $this->slots[$this->currentComponent()][$name] = '';

            $this->slotStack[$this->currentComponent()][] = [$name, $attributes];
        }
    }

    /**
     * Save the slot content for rendering.
	 * 保存槽内容以供呈现
     *
     * @return void
     */
    public function endSlot()
    {
        last($this->componentStack);

        $currentSlot = array_pop(
            $this->slotStack[$this->currentComponent()]
        );

        [$currentName, $currentAttributes] = $currentSlot;

        $this->slots[$this->currentComponent()][$currentName] = new ComponentSlot(
            trim(ob_get_clean()), $currentAttributes
        );
    }

    /**
     * Get the index for the current component.
	 * 获取当前组件的索引
     *
     * @return int
     */
    protected function currentComponent()
    {
        return count($this->componentStack) - 1;
    }

    /**
     * Flush all of the component state.
	 * 刷新所有组件状态
     *
     * @return void
     */
    protected function flushComponents()
    {
        $this->componentStack = [];
        $this->componentData = [];
        $this->currentComponentData = [];
    }
}
