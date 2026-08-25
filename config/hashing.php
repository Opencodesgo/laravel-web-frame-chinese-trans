<?php
/**
 * 配置，哈希
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver	默认哈希驱动
    |--------------------------------------------------------------------------
    |
    | This option controls the default hash driver that will be used to hash
    | passwords for your application. By default, the bcrypt algorithm is
    | used; however, you remain free to modify this option if you wish.
	| 这个选项控制默认的哈希驱动程序。默认使用 bcrypt 算法，但如果您愿意，仍可自由修改此选项。
    |
    | Supported: "bcrypt", "argon", "argon2id"
    |
    */

    'driver' => 'bcrypt',

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Options 	Bcrypt算法选项
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration options that should be used when
    | passwords are hashed using the Bcrypt algorithm. This will allow you
    | to control the amount of time it takes to hash the given password.
	| 在这里，您可以指定在以下情况应该使用的配置选项密码使用Bcrypt算法散列。
	| 这将允许你控制给定密码哈希所需的时间。
    |
    */

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon Options 	Argon算法选项
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration options that should be used when
    | passwords are hashed using the Argon algorithm. These will allow you
    | to control the amount of time it takes to hash the given password.
	| 在这里，您可以指定在以下情况应该使用的配置选项密码使用Argon算法散列。
	| 这些选项将允许您控制给定密码哈希所需的时间。
    |
    */

    'argon' => [
        'memory' => 65536,
        'threads' => 1,
        'time' => 4,
    ],

];
