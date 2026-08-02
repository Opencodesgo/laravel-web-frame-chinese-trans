<?php
/**
 * Illuminate，通知，应通知的
 */

namespace Illuminate\Notifications;

trait Notifiable
{
    use HasDatabaseNotifications, RoutesNotifications;
}
