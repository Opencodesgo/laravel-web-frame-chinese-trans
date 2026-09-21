<?php
/**
 * Illuminate，通知，数据库通知收集
 */

namespace Illuminate\Notifications;

use Illuminate\Database\Eloquent\Collection;

class DatabaseNotificationCollection extends Collection
{
    /**
     * Mark all notifications as read.
	 * 将所有通知标记为已读
     *
     * @return void
     */
    public function markAsRead()
    {
        $this->each->markAsRead();
    }

    /**
     * Mark all notifications as unread.
	 * 将所有通知标记为未读
     *
     * @return void
     */
    public function markAsUnread()
    {
        $this->each->markAsUnread();
    }
}
