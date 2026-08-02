<?php
/**
 * 路由，api 路由
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes 	API路由
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
| 你能在这里为你的应用注册API路由。
| 这些路由被RouteServiceProvider加载成一个组，它被分配"api"中件间组。
| 享受构建你的API！
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
