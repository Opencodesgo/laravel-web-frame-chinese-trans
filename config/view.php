<?php
/**
 * 配置，视图
 */

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths 	视图存储路径
    |--------------------------------------------------------------------------
    |
    | Most templating systems load templates from disk. Here you may specify
    | an array of paths that should be checked for your views. Of course
    | the usual Laravel view path has already been registered for you.
	| 大多数模板系统从磁盘加载模板。在这里,您可以指定一个应该检查您视图的路径数组。
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path 	已编译视图路径
    |--------------------------------------------------------------------------
    |
    | This option determines where all the compiled Blade templates will be
    | stored for your application. Typically, this is within the storage
    | directory. However, as usual, you are free to change this value.
	| 这个选项决定了所有将被编译为您的应用程序存储Blade模板。
    |
    */

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),

];
