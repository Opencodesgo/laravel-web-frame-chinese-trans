<?php
/**
 * Illuminate，契约，翻译，翻译程序
 */

namespace Illuminate\Contracts\Translation;

interface Translator
{
    /**
     * Get the translation for a given key.
	 * 得到给定主键的翻译
     *
     * @param  string  $key
     * @param  array  $replace
     * @param  string|null  $locale
     * @return mixed
     */
    public function get($key, array $replace = [], $locale = null);

    /**
     * Get a translation according to an integer value.
	 * 根据整数值获取翻译
     *
     * @param  string  $key
     * @param  \Countable|int|array  $number
     * @param  array  $replace
     * @param  string|null  $locale
     * @return string
     */
    public function choice($key, $number, array $replace = [], $locale = null);

    /**
     * Get the default locale being used.
	 * 获取正在使用的默认区域设置
     *
     * @return string
     */
    public function getLocale();

    /**
     * Set the default locale.
	 * 设置默认语言环境
     *
     * @param  string  $locale
     * @return void
     */
    public function setLocale($locale);
}
