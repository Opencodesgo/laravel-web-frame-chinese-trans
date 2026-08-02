<?php
/**
 * Illuminate，支持，消息包
 */

namespace Illuminate\Support;

use Illuminate\Contracts\Support\Htmlable;

class HtmlString implements Htmlable
{
    /**
     * The HTML string.
	 * HTML字符串
     *
     * @var string
     */
    protected $html;

    /**
     * Create a new HTML string instance.
	 * 创建一个新的HTML字符串实例
     *
     * @param  string  $html
     * @return void
     */
    public function __construct($html = '')
    {
        $this->html = $html;
    }

    /**
     * Get the HTML string.
	 * 得到HTML字符串
     *
     * @return string
     */
    public function toHtml()
    {
        return $this->html;
    }

    /**
     * Determine if the given HTML string is empty.
	 * 确定给定的HTML字符串是否为空
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->html === '';
    }

    /**
     * Get the HTML string.
	 * 获取HTML字符串
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toHtml();
    }
}
