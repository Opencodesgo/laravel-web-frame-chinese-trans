<?php
/**
 * 引导, app应用
 */

/*
|--------------------------------------------------------------------------
| Create The Application	创建应用
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
| 我们要做的第一件事是创建一个新的Laravel实例，充当Laravel所有组件的"粘合剂"，
| 用于绑定所有不同部分的系统IoC容器。
|
*/

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces		绑定重要接口
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
| 接下来，我们需要将一些重要的接口绑定到容器中，以便我们将能够在需要的时候解决这些问题。
| 内核服务从web和CLI向应用传入的请求。
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Return The Application	返回应用
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
| 这个脚本返回应用实例。
| 实例被赋予调用脚本，这样我们就可以分离实例的构建，并从应用的实例运行和发送响应。
|
*/

return $app;
