<?php
/**
 * Illuminate，视图，引擎，引擎抽象类
 */

namespace Illuminate\View\Engines;

abstract class Engine
{
    /**
     * The view that was last to be rendered.
	 * 最后要呈现的视图
     *
     * @var string
     */
    protected $lastRendered;

    /**
     * Get the last view that was rendered.
	 * 获取最后一次渲染的视图
     *
     * @return string
     */
    public function getLastRendered()
    {
        return $this->lastRendered;
    }
}
