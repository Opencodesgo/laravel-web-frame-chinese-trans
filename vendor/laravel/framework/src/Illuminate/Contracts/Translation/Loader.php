<?php
/**
 * Illuminate，契约，翻译，加载器
 */

namespace Illuminate\Contracts\Translation;

interface Loader
{
    /**
     * Load the messages for the given locale.
	 * 加载给定区域设置的消息
     *
     * @param  string  $locale
     * @param  string  $group
     * @param  string|null  $namespace
     * @return array
     */
    public function load($locale, $group, $namespace = null);

    /**
     * Add a new namespace to the loader.
	 * 向加载器添加一个新的命名空间
     *
     * @param  string  $namespace
     * @param  string  $hint
     * @return void
     */
    public function addNamespace($namespace, $hint);

    /**
     * Add a new JSON path to the loader.
	 * 向加载器添加一个新的JSON路径
     *
     * @param  string  $path
     * @return void
     */
    public function addJsonPath($path);

    /**
     * Get an array of all the registered namespaces.
	 * 获取所有已注册名称空间的数组
     *
     * @return array
     */
    public function namespaces();
}
