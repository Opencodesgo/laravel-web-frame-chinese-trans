<?php
/**
 * 路由，Web路由
 */

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes	Web路由
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
| 这里您可以为应用注册Web路由。
| 这些路由是由组中的RouteServiceProvider加载的包含"web"中间件组。
| 现在创造伟大的东西!
|
*/

Route::get('/', function () {
    return view('welcome');
});
