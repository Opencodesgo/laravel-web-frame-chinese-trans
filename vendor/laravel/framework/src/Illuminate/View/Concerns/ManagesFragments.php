<?php
/**
 * Illuminate，视图，问题，管理片段
 */

namespace Illuminate\View\Concerns;

use InvalidArgumentException;

trait ManagesFragments
{
    /**
     * All of the captured, rendered fragments.
	 * 所有捕获的，渲染的碎片。
     *
     * @var array
     */
    protected $fragments = [];

    /**
     * The stack of in-progress fragment renders.
	 * 正在进行的片段渲染的堆栈
     *
     * @var array
     */
    protected $fragmentStack = [];

    /**
     * Start injecting content into a fragment.
	 * 开始向片段中注入内容
     *
     * @param  string  $fragment
     * @return void
     */
    public function startFragment($fragment)
    {
        if (ob_start()) {
            $this->fragmentStack[] = $fragment;
        }
    }

    /**
     * Stop injecting content into a fragment.
	 * 停止向片段中注入内容
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function stopFragment()
    {
        if (empty($this->fragmentStack)) {
            throw new InvalidArgumentException('Cannot end a fragment without first starting one.');
        }

        $last = array_pop($this->fragmentStack);

        $this->fragments[$last] = ob_get_clean();

        return $this->fragments[$last];
    }

    /**
     * Get the contents of a fragment.
	 * 获取片段的内容
     *
     * @param  string  $name
     * @param  string|null  $default
     * @return mixed
     */
    public function getFragment($name, $default = null)
    {
        return $this->getFragments()[$name] ?? $default;
    }

    /**
     * Get the entire array of rendered fragments.
	 * 获取渲染片段的整个数组
     *
     * @return array
     */
    public function getFragments()
    {
        return $this->fragments;
    }

    /**
     * Flush all of the fragments.
	 * 冲洗所有的碎片
     *
     * @return void
     */
    public function flushFragments()
    {
        $this->fragments = [];
        $this->fragmentStack = [];
    }
}
