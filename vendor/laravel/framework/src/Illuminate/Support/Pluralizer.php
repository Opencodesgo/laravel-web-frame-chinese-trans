<?php
/**
 * Illuminate, 支持, Pluralizer
 */

namespace Illuminate\Support;

use Doctrine\Inflector\InflectorFactory;

class Pluralizer
{
    /**
     * The cached inflector instance.
	 * 缓存的影响器实例
     *
     * @var static
     */
    protected static $inflector;

    /**
     * The language that should be used by the inflector.
	 * 屈折器应该使用的语言
     *
     * @var string
     */
    protected static $language = 'english';

    /**
     * Uncountable non-nouns word forms.
	 * 不可数非名词词的形式
     *
     * Contains words supported by Doctrine/Inflector/Rules/English/Uninflected.php
	 * 包含Doctrine/Inflector/Rules/English/ uninflection .php支持的单词
     *
     * @var string[]
     */
    public static $uncountable = [
        'recommended',
        'related',
    ];

    /**
     * Get the plural form of an English word.
	 * 了解英语单词的复数形式
     *
     * @param  string  $value
     * @param  int|array|\Countable  $count
     * @return string
     */
    public static function plural($value, $count = 2)
    {
        if (is_countable($count)) {
            $count = count($count);
        }

        if ((int) abs($count) === 1 || static::uncountable($value) || preg_match('/^(.*)[A-Za-z0-9\x{0080}-\x{FFFF}]$/u', $value) == 0) {
            return $value;
        }

        $plural = static::inflector()->pluralize($value);

        return static::matchCase($plural, $value);
    }

    /**
     * Get the singular form of an English word.
	 * 获取英语单词的单数形式
     *
     * @param  string  $value
     * @return string
     */
    public static function singular($value)
    {
        $singular = static::inflector()->singularize($value);

        return static::matchCase($singular, $value);
    }

    /**
     * Determine if the given value is uncountable.
	 * 确定给定的值是否不可数
     *
     * @param  string  $value
     * @return bool
     */
    protected static function uncountable($value)
    {
        return in_array(strtolower($value), static::$uncountable);
    }

    /**
     * Attempt to match the case on two strings.
	 * 尝试在两个字符串上匹配大小写
     *
     * @param  string  $value
     * @param  string  $comparison
     * @return string
     */
    protected static function matchCase($value, $comparison)
    {
        $functions = ['mb_strtolower', 'mb_strtoupper', 'ucfirst', 'ucwords'];

        foreach ($functions as $function) {
            if ($function($comparison) === $comparison) {
                return $function($value);
            }
        }

        return $value;
    }

    /**
     * Get the inflector instance.
	 * 获取影响因子实例
     *
     * @return \Doctrine\Inflector\Inflector
     */
    public static function inflector()
    {
        if (is_null(static::$inflector)) {
            static::$inflector = InflectorFactory::createForLanguage(static::$language)->build();
        }

        return static::$inflector;
    }

    /**
     * Specify the language that should be used by the inflector.
	 * 指定影响因子应使用的语言
     *
     * @param  string  $language
     * @return void
     */
    public static function useLanguage(string $language)
    {
        static::$language = $language;

        static::$inflector = null;
    }
}
