<?php
/**
 * Illuminate，基础，事件，语言环境更新
 */

namespace Illuminate\Foundation\Events;

class LocaleUpdated
{
    /**
     * The new locale.
	 * 新的区域设置
     *
     * @var string
     */
    public $locale;

    /**
     * Create a new event instance.
	 * 创建新的事件实例
     *
     * @param  string  $locale
     * @return void
     */
    public function __construct($locale)
    {
        $this->locale = $locale;
    }
}
