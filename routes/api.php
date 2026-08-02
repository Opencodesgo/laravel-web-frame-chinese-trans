<?php
/**
 * 路由，API路由
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
| 在这里你可以为你的应用程序注册API路由。
| 在此处可以为您的应用程序注册 API 路由。这些路由由 RouteServiceProvider 在被分配了“api”中间件组的组中加载。
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
