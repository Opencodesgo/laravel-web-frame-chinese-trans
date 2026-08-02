<?php
/**
 * Illuminate，视图，匿名组件
 */

namespace Illuminate\View;

class AnonymousComponent extends Component
{
    /**
     * The component view.
	 * 视图
     *
     * @var string
     */
    protected $view;

    /**
     * The component data.
	 * 数据
     *
     * @var array
     */
    protected $data = [];

    /**
     * Create a new anonymous component instance.
	 * 创建一个新的匿名组件实例
     *
     * @param  string  $view
     * @param  array  $data
     * @return void
     */
    public function __construct($view, $data)
    {
        $this->view = $view;
        $this->data = $data;
    }

    /**
     * Get the view / view contents that represent the component.
	 * 得到视图内容
     *
     * @return string
     */
    public function render()
    {
        return $this->view;
    }

    /**
     * Get the data that should be supplied to the view.
	 * 得到应该提供给视图的数据
     *
     * @return array
     */
    public function data()
    {
        $this->attributes = $this->attributes ?: new ComponentAttributeBag;

        return $this->data + ['attributes' => $this->attributes];
    }
}
