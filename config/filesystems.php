<?php
/**
 * 配置，文件系统
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk 	默认文件系统磁盘
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
	| 这里您可以指定应该使用的默认文件系统磁盘的框架。
	| 您的应用程序可使用本地磁盘以及多种云存储盘。只需轻松存储即可！
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks 	文件系统磁盘
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
	| 在这里，您可以根据需要配置尽可能多的文件系统"磁盘"甚至可以配置相同驱动程序的多个磁盘。
	| 每个驱动程序均已设置默认值，作为所需选项的示例。
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links 符号链接	
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
	| 时将创建的符号链接在这里可以配置'storage:link'工匠命令执行完毕。
	| 数组的键应为链接的位置，值应为其目标。
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
