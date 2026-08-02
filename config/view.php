<?php
/**
 * 配置，视图
 */

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths	视图存储路径
    |--------------------------------------------------------------------------
    |
    | Most templating systems load templates from disk. Here you may specify
    | an array of paths that should be checked for your views. Of course
    | the usual Laravel view path has already been registered for you.
	| 大多数模板系统从磁盘加载模板。
	| 在此处，您可以指定一个需要检查视图的路径数组。当然，常规的 Laravel 视图路径已经为您注册好了。
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path 	编译视图路径
    |--------------------------------------------------------------------------
    |
    | This option determines where all the compiled Blade templates will be
    | stored for your application. Typically, this is within the storage
    | directory. However, as usual, you are free to change this value.
	| 此选项决定了应用程序中所有编译的Blade模板的存储位置。
	| 通常，这个值位于存储目录中。但和往常一样，你可以自由修改此值。
    |
    */

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),

];
