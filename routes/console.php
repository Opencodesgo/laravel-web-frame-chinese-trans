<?php
/**
 * 路由，控制台
 */

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes	控制台路由
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
| 您可以在些文件中定义所有基于控制台指令的闭包。
| 每个闭包绑定到一个命令实例，允许与每个命令的IO方法交互的简单方法。
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
