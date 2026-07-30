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
| 这里你可以为你的应用注册web路由。
| 这些路由在包含“web”中间件组的组内由RouteServiceProvider加载。现在创造伟大的东西!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// 增加index路由 localhost:9021/index，自己加可以删除！
Route::get('/index', 'IndexController@index');
