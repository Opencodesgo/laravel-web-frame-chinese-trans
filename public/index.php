<?php
/**
 * 公共，index 入口
 */

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

define('LARAVEL_START', microtime(true));

/**
 * 这是应用的所有请求入口，所有请求都会进web服务器导向这个文件。
 * index.php 文件包含的代码并不多，但是，这里是加载框架其他部分的起点。
 */

/*
|--------------------------------------------------------------------------
| Register The Auto Loader	注册自动加载程序
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels great to relax.
| Composer 为应用提供了一个方便的、自动生成的类装入器。
| 我们只需要利用它！我们只需要简单引入到脚本中，这样我们就不必担心手动加载。
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Turn On The Lights	点亮
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
| 我们需要点亮PHP开发，所以让我们打开灯。
| 这将引导框架并使其准备好使用，它将加载应用以便我们能运行它并发送响应
| 至浏览器并取悦我们的用户。
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application	运行应用
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
| 一旦有了应用，我们可以通过内核处理传入的请求，并将相关响应发送回浏览器，
| 让他们享受我们为他们准备的创意和美好的应用。
|
*/

// Illuminate\Container\Container，App\Http\Kernel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Illuminate\Foundation\Http\Kernel, public function handle($request)
$response = $kernel->handle(
	// Illuminate\Http\Request, public static function capture()
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
