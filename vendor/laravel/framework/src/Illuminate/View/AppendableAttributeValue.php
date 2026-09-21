<?php
/**
 * Illuminate，视图，可附加属性值
 */

namespace Illuminate\View;

class AppendableAttributeValue
{
    /**
     * The attribute value.
	 * 属性值
     *
     * @var mixed
     */
    public $value;

    /**
     * Create a new appendable attribute value.
	 * 创建新的可追加属性值
     *
     * @param  mixed  $value
     * @return void
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Get the string value.
	 * 得到字符串值
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }
}
