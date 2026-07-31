<?php
/**
 * 路由，Web 路由
 */

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes 	Web路由
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
| 在这里可以为您的应用程序注册 Web 路由。这些路由由包含“web”中间件组的组内的 RouteServiceProvider 加载。
| 现在创造一些伟大的东西！
|
*/

Route::get('/', function () {
    return view('welcome');
});
