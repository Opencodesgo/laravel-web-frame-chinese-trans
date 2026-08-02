<?php
/**
 * Illuminate，广播，信道
 */

namespace Illuminate\Broadcasting;

class Channel
{
    /**
     * The channel's name.
	 * 信道名称
     *
     * @var string
     */
    public $name;

    /**
     * Create a new channel instance.
	 * 创建信道实例
     *
     * @param  string  $name
     * @return void
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Convert the channel instance to a string.
	 * 将信道实例转换为字符串
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
