<?php
/**
 * 配置，认证
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults 	认证默认
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
	| 这个选项控制默认的身份验证"守卫"和密码重置选项为您的应用程序。
	| 你可以根据需要更改这些默认设置，但它们对大多数应用程序来说都是一个完美的起点。
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards 	身份验证守卫
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
	| 接下来，您可以为您的应用程序定义每个身份验证保护。当然，已经为您定义了一个很棒的默认配置，它使用会话存储和Eloquent用户提供程序。
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
	| 所有身份验证驱动程序都有一个用户提供程序。这定义了用户如何从数据库或其他用于持久化用户数据的应用程序所使用的存储机制中实际获取。
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers 	用户提供者
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
	| 所有身份验证驱动程序都有一个用户提供程序。
	| 这定义了用户如何从数据库或其他用于持久化用户数据的应用程序所使用的存储机制中实际获取。
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
	| 如果您有多个用户表或模型，您可以配置表示每个模型/表的多个源。
	| 这些源随后可以分配给您已定义的任何额外身份验证守护程序。
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords 	重置密码
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
	| 您可以指定多个密码重置配置，如果您有更多不止一个用户表或模型在应用程序中，您希望拥有单独密码重置根据具体用户类型进行设置。
    |
    | The expire time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
	| 过期时间是指每个重置令牌被视为有效的分钟数。
	| 此安全功能可使令牌保持较短的生命周期，从而减少被猜测的时间。您可以根据需要进行调整。
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout 	密码确认超时时间
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | times out and the user is prompted to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
	| 在这里，您可以定义密码确认前的秒数超时，并提示用户通过屏幕确认。
	| 缺省情况下，超时时间为3小时。
    |
    */

    'password_timeout' => 10800,

];
