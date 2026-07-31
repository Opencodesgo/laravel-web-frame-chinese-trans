<?php
/**
 * Illuminate，视图，匿名组件
 */

namespace Illuminate\View;

class AnonymousComponent extends Component
{
    /**
     * The component view.
	 * 组件视图
     *
     * @var string
     */
    protected $view;

    /**
     * The component data.
	 * 组件数据
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
	 * 获取表示组件的视图/视图内容
     *
     * @return string
     */
    public function render()
    {
        return $this->view;
    }

    /**
     * Get the data that should be supplied to the view.
	 * 获取应该提供给视图的数据
     *
     * @return array
     */
    public function data()
    {
        $this->attributes = $this->attributes ?: $this->newAttributeBag();

        return array_merge(
            optional($this->data['attributes'] ?? null)->getAttributes() ?: [],
            $this->attributes->getAttributes(),
            $this->data,
            ['attributes' => $this->attributes]
        );
    }
}
