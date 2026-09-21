<?php
/**
 * 公共，index入口
 */

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance		检查应用是是否为维护模式
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
| 如果应用程序处于维护/演示模式，我们将加载此文件，以便显示任何预渲染的内容，
| 而不是启动框架，这可能会导致异常。
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader	注册自动加载程序
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
| Composer提供了一个方便的、自动生成的应用类装入器。
| 我们只需要利用它！我们只需引入它到这里的脚本中，这样我们就不需要手动加载我们的类。
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application	运行应用
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
| 一旦有了应用程序，我们就可以使用应用程序的HTTP内核。
| 然后，我们将返回响应至客户端的浏览器，允许他们享受我们的应用。
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
