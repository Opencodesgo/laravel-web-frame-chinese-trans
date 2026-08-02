<?php
/**
 * Illuminate，通知，消息，数据库信息
 */

namespace Illuminate\Notifications\Messages;

class DatabaseMessage
{
    /**
     * The data that should be stored with the notification.
	 * 应与通知一起存储的数据
     *
     * @var array
     */
    public $data = [];

    /**
     * Create a new database message.
	 * 创建新的数据库消息
     *
     * @param  array  $data
     * @return void
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }
}
