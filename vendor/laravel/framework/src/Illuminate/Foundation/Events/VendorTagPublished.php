<?php
/**
 * Illuminate，基础，事件，供应商标签发布
 */

namespace Illuminate\Foundation\Events;

class VendorTagPublished
{
    /**
     * The vendor tag that was published.
	 * 发布的供应商标签
     *
     * @var string
     */
    public $tag;

    /**
     * The publishable paths registered by the tag.
	 * 由标记注册的可发布路径
     *
     * @var array
     */
    public $paths;

    /**
     * Create a new event instance.
	 * 创建新的事件实例
     *
     * @param  string  $tag
     * @param  array  $paths
     * @return void
     */
    public function __construct($tag, $paths)
    {
        $this->tag = $tag;
        $this->paths = $paths;
    }
}
