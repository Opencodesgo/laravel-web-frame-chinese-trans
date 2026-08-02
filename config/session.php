<?php
/**
 * 配置，Session会话
 */

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Session Driver 	默认会话驱动
    |--------------------------------------------------------------------------
    |
    | This option controls the default session "driver" that will be used on
    | requests. By default, we will use the lightweight native driver but
    | you may specify any of the other wonderful drivers provided here.
	| 这个选项控制将被请求的默认会话"驱动程序"。
	| 默认情况下，我们将使用轻量级的原生驱动程序，但您也可以指定此处提供的其他任何优秀驱动程序。
    |
    | Supported: "file", "cookie", "database", "apc",
    |            "memcached", "redis", "dynamodb", "array"
    |
    */

    'driver' => env('SESSION_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Session Lifetime 	会话生命周期
    |--------------------------------------------------------------------------
    |
    | Here you may specify the number of minutes that you wish the session
    | to be allowed to remain idle before it expires. If you want them
    | to immediately expire on the browser closing, set that option.
	| 在这里，您可以指定希望会话在到期前保持空闲的分钟数。
	| 如果希望它们在浏览器关闭时立即失效，请设置该选项。
    |
    */

    'lifetime' => env('SESSION_LIFETIME', 120),

    'expire_on_close' => false,

    /*
    |--------------------------------------------------------------------------
    | Session Encryption 	会话加密
    |--------------------------------------------------------------------------
    |
    | This option allows you to easily specify that all of your session data
    | should be encrypted before it is stored. All encryption will be run
    | automatically by Laravel and you can use the Session like normal.
	| 此选项允许您轻松指定在存储之前应对所有会话数据进行加密。
	| 所有加密将由 Laravel 自动处理，你可以像平常一样使用 Session。
    |
    */

    'encrypt' => false,

    /*
    |--------------------------------------------------------------------------
    | Session File Location 	会话文件位置
    |--------------------------------------------------------------------------
    |
    | When using the native session driver, we need a location where session
    | files may be stored. A default has been set for you but a different
    | location may be specified. This is only needed for file sessions.
	| 使用本机会话驱动程序时，我们需要一个可以存储会话文件的位置。
	| 已为您设置默认位置，但可能指定不同的位置。此设置仅适用于文件会话。
    |
    */

    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Session Database Connection 	会话数据库连接
    |--------------------------------------------------------------------------
    |
    | When using the "database" or "redis" session drivers, you may specify a
    | connection that should be used to manage these sessions. This should
    | correspond to a connection in your database configuration options.
	| 当使用"数据库"或"redis"会话驱动程序时，您可以指定一个用于管理这些会话的连接。
	| 这应该对应数据库配置选项中的一个连接。
    |
    */

    'connection' => env('SESSION_CONNECTION', null),

    /*
    |--------------------------------------------------------------------------
    | Session Database Table 	会话数据库表
    |--------------------------------------------------------------------------
    |
    | When using the "database" session driver, you may specify the table we
    | should use to manage the sessions. Of course, a sensible default is
    | provided for you; however, you are free to change this as needed.
	| 使用"数据库"会话驱动程序时，您可以指定我们应该用来管理会话的表。
	| 当然，系统为您提供了合理的默认设置；但您可以根据需要随时更改。
    |
    */

    'table' => 'sessions',

    /*
    |--------------------------------------------------------------------------
    | Session Cache Store 	会话缓存存储
    |--------------------------------------------------------------------------
    |
    | While using one of the framework's cache driven session backends you may
    | list a cache store that should be used for these sessions. This value
    | must match with one of the application's configured cache "stores".
	| 在使用框架的缓存驱动会话后端时，您可以列出应用于这些会话的缓存存储。
	| 该值必须与应用程序配置的缓存“存储”之一匹配。
    |
    | Affects: "apc", "dynamodb", "memcached", "redis"
    |
    */

    'store' => env('SESSION_STORE', null),

    /*
    |--------------------------------------------------------------------------
    | Session Sweeping Lottery	会话扫描随机选择
    |--------------------------------------------------------------------------
    |
    | Some session drivers must manually sweep their storage location to get
    | rid of old sessions from storage. Here are the chances that it will
    | happen on a given request. By default, the odds are 2 out of 100.
	| 一些会话驱动程序必须手动清除其存储位置，以从存储中删除旧会话。
	| 以下是每次请求发生该情况的概率。默认情况下，概率为2%。
    |
    */

    'lottery' => [2, 100],

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Name	会话Cookie名称
    |--------------------------------------------------------------------------
    |
    | Here you may change the name of the cookie used to identify a session
    | instance by ID. The name specified here will get used every time a
    | new session cookie is created by the framework for every driver.
	| 在这里，您可以更改用于通过ID标识会话实例的cookie的名称。
	| 此处指定的名称将在框架为每个驱动程序创建新会话 Cookie 时被使用。
    |
    */

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug(env('APP_NAME', 'laravel'), '_').'_session'
    ),

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Path	会话Cookie路径
    |--------------------------------------------------------------------------
    |
    | The session cookie path determines the path for which the cookie will
    | be regarded as available. Typically, this will be the root path of
    | your application but you are free to change this when necessary.
	| 会话cookie路径决定了cookie将被视为可用的路径。
	| 通常，这将是您的应用程序的根路径，但您可根据需要自由更改。
    |
    */

    'path' => '/',

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Domain		会话Cookie域
    |--------------------------------------------------------------------------
    |
    | Here you may change the domain of the cookie used to identify a session
    | in your application. This will determine which domains the cookie is
    | available to in your application. A sensible default has been set.
	| 在这里，您可以更改用于标识应用程序中会话的cookie的域。
	| 这将决定该 Cookie 在您的应用程序中可使用的域名。已设置合理的默认值。
    |
    */

    'domain' => env('SESSION_DOMAIN', null),

    /*
    |--------------------------------------------------------------------------
    | HTTPS Only Cookies	仅支持HTTPS的cookie
    |--------------------------------------------------------------------------
    |
    | By setting this option to true, session cookies will only be sent back
    | to the server if the browser has a HTTPS connection. This will keep
    | the cookie from being sent to you when it can't be done securely.
	| 通过将此选项设置为true，只有当浏览器具有HTTPS连接时，会话Cookie才会被发送回服务器。
	| 这将防止在无法安全传输时，该 Cookie 被发送给您。
    |
    */

    'secure' => env('SESSION_SECURE_COOKIE'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Access Only		仅HTTP访问
    |--------------------------------------------------------------------------
    |
    | Setting this value to true will prevent JavaScript from accessing the
    | value of the cookie and the cookie will only be accessible through
    | the HTTP protocol. You are free to modify this option if needed.
	| 将此值设置为true将阻止JavaScript访问cookie的值，并且cookie只能通过HTTP协议访问。
	| 如果需要，您可以自由修改此选项。
    |
    */

    'http_only' => true,

    /*
    |--------------------------------------------------------------------------
    | Same-Site Cookies 	同站点Cookie
    |--------------------------------------------------------------------------
    |
    | This option determines how your cookies behave when cross-site requests
    | take place, and can be used to mitigate CSRF attacks. By default, we
    | will set this value to "lax" since this is a secure default value.
	| 此选项决定了您的Cookie在发生跨站点请求时的行为，可用于减轻CSRF攻击。
	| 默认情况下，我们将此值设置为“lax”，因为这是一个安全的默认值。
    |
    | Supported: "lax", "strict", "none", null
    |
    */

    'same_site' => 'lax',

];
