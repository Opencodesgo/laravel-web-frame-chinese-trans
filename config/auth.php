<?php
/**
 * 配置，auth
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults   身份验证默认值
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
	| 该选项控制默认的身份验证“保护”和密码重置选项。
	| 您可以根据需要更改这些默认值,但对于大多数应用程序来说,它们是一个完美的开始。
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards     身份验证守卫
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
	| 接下来，您可以为应用程序定义每个身份验证器。
	| 当然,在这里为您定义了一个巨大的默认配置,它使用会话存储和有能力的用户提供者。
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
	| 所有身份验证驱动程序都有一个用户提供者。
	| 这定义了用户如何从您的数据库或其他存储机制检索来保存您的用户的数据。
    |
    | Supported: "session", "token"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'token',
            'provider' => 'users',
            'hash' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers    用户提供者
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
	| 所有身份验证驱动程序都有一个用户提供者。
	| 这定义了如何从您的数据库或应用程序使用的其他存储机制中检索到用户的数据。
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
	| 如果您有多个用户表或模型,您可以配置代表每个模型/表的多个源。
	| 这些消息来源可能会被分配给你定义的任何额外的身份验证保护。
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\User::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords   重置密码
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
	| 如果在应用程序中有多个用户表或模型,您可以指定多个密码重置配置,
	| 并且您希望根据特定的用户类型进行单独的密码重置设置。
    |
    | The expire time is the number of minutes that the reset token should be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
	| 过期时间是重置令牌应该被认为有效的时间。
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
    | Password Confirmation Timeout   密码确认超时时间  
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | times out and the user is prompted to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
	| 在这里,您可以定义密码确认时间之前的几秒钟,用户被提示通过确认屏幕重新输入他们的密码。
	| 默认情况下,超时持续3个小时。
    |
    */

    'password_timeout' => 10800,

];
