<?php
/**
 * 路由，控制台
 */

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes 	控制台路由
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
| 此文件用于定义您所有的基于 Closure 的控制台命令。
| 每个闭包都绑定到一个命令实例，从而可以轻松地调用每个命令的输入输出方法。
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
