<?php
/**
 * 路由，频道
 */

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels	广播频道
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
| 在这里，您可以注册应用支持的所有事件广播频道。
| 给定的通道授权回调是用来检查经过身份验证的用户是否可以侦听通道。
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
