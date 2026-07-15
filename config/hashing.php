<?php
/**
 * 配置，哈希
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver   默认哈希驱动
    |--------------------------------------------------------------------------
    |
    | This option controls the default hash driver that will be used to hash
    | passwords for your application. By default, the bcrypt algorithm is
    | used; however, you remain free to modify this option if you wish.
	| 此选项控制将用于哈希的默认哈希驱动器。
	| 默认情况下,使用bcrypt算法;但是,如果您愿意,您可以自由修改该选项。
    |
    | Supported: "bcrypt", "argon", "argon2id"
    |
    */

    'driver' => 'bcrypt',

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Options    加密选项
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration options that should be used when
    | passwords are hashed using the Bcrypt algorithm. This will allow you
    | to control the amount of time it takes to hash the given password.
	| 在这里,您可以指定在使用Bcrypt算法时使用的配置选项。
	| 这将允许您控制用于散列给定密码的时间。
    |
    */

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon Options     Argon选项
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration options that should be used when
    | passwords are hashed using the Argon algorithm. These will allow you
    | to control the amount of time it takes to hash the given password.
	| 在这里，你可以指定应该使用何时使用的配置选项。
	| 这些将允许您控制用于散列给定密码的时间。
    |
    */

    'argon' => [
        'memory' => 1024,
        'threads' => 2,
        'time' => 2,
    ],

];
