<?php
/**
 * Illuminate，视图，组件槽
 */

namespace Illuminate\View;

use Illuminate\Contracts\Support\Htmlable;

class ComponentSlot implements Htmlable
{
    /**
     * The slot attribute bag.
	 * 槽位属性包
     *
     * @var \Illuminate\View\ComponentAttributeBag
     */
    public $attributes;

    /**
     * The slot contents.
	 * 插槽内容
     *
     * @var string
     */
    protected $contents;

    /**
     * Create a new slot instance.
	 * 创建一个新的槽实例
     *
     * @param  string  $contents
     * @param  array  $attributes
     * @return void
     */
    public function __construct($contents = '', $attributes = [])
    {
        $this->contents = $contents;

        $this->withAttributes($attributes);
    }

    /**
     * Set the extra attributes that the slot should make available.
	 * 设置插槽应该提供的额外属性
     *
     * @param  array  $attributes
     * @return $this
     */
    public function withAttributes(array $attributes)
    {
        $this->attributes = new ComponentAttributeBag($attributes);

        return $this;
    }

    /**
     * Get the slot's HTML string.
	 * 获取插槽的HTML字符串
     *
     * @return string
     */
    public function toHtml()
    {
        return $this->contents;
    }

    /**
     * Determine if the slot is empty.
	 * 确定槽位是否为空
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->contents === '';
    }

    /**
     * Determine if the slot is not empty.
	 * 确定槽位是否为空
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return ! $this->isEmpty();
    }

    /**
     * Get the slot's HTML string.
	 * 获取插槽的HTML字符串
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toHtml();
    }
}
