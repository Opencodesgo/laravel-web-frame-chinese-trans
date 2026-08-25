<?php
/**
 * 公共，index入口
 */

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance 	检查应用程序是否处于维护状态
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
| 如果应用程序处于维护/演示模式，通过"down"命令我们将加载这个文件，
| 这样就可以显示任何预呈现的内容而不是启动框架，这可能会导致异常。
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader 	注册自动加载程序
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
| Composer为应用提供了一个方便的、自动生成的类装入器。
| 我们只需要利用它！我们将简单引入它到脚本中，这样我们就不需要手动加载我们的类。
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application 	运行应用
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
| 一旦有了应用程序，我们就可以使用应用的HTTP内核。
| 接下来，我们将发送响应至这个客户端的浏览器，允许他们享受我们的应用程序。
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

// Illuminate\Container\Container	751 public function make($abstract, array $parameters = [])
$kernel = $app->make(Kernel::class);

// Illuminate\Foundation\Application  1052  public function handle(SymfonyRequest $request, int $type = self::MASTER_REQUEST, bool $catch = true)
$response = $kernel->handle(
	// Illuminate\Http\Request 	68  public static function capture()
    $request = Request::capture()
)->send();	// Illuminate\Http\Response Symfony\Component\HttpFoundation\Response 	403 public function send()

$kernel->terminate($request, $response);
