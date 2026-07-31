<?php
/**
 * Illuminate，支持，字符串 
 */

namespace Illuminate\Support;

use Closure;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Traits\Tappable;
use JsonSerializable;
use Symfony\Component\VarDumper\VarDumper;

class Stringable implements JsonSerializable
{
    use Conditionable, Macroable, Tappable;

    /**
     * The underlying string value.
	 * 底层字符串值
     *
     * @var string
     */
    protected $value;

    /**
     * Create a new instance of the class.
	 * 创建类的新实例
     *
     * @param  string  $value
     * @return void
     */
    public function __construct($value = '')
    {
        $this->value = (string) $value;
    }

    /**
     * Return the remainder of a string after the first occurrence of a given value.
	 * 返回给定值第一次出现后字符串的剩余部分
     *
     * @param  string  $search
     * @return static
     */
    public function after($search)
    {
        return new static(Str::after($this->value, $search));
    }

    /**
     * Return the remainder of a string after the last occurrence of a given value.
	 * 返回给定值最后一次出现后字符串的剩余部分
     *
     * @param  string  $search
     * @return static
     */
    public function afterLast($search)
    {
        return new static(Str::afterLast($this->value, $search));
    }

    /**
     * Append the given values to the string.
	 * 将给定的值追加到字符串
     *
     * @param  array  $values
     * @return static
     */
    public function append(...$values)
    {
        return new static($this->value.implode('', $values));
    }

    /**
     * Transliterate a UTF-8 value to ASCII.
	 * 将UTF-8值音译为ASCII
     *
     * @param  string  $language
     * @return static
     */
    public function ascii($language = 'en')
    {
        return new static(Str::ascii($this->value, $language));
    }

    /**
     * Get the trailing name component of the path.
	 * 获取路径的尾随名称组件
     *
     * @param  string  $suffix
     * @return static
     */
    public function basename($suffix = '')
    {
        return new static(basename($this->value, $suffix));
    }

    /**
     * Get the basename of the class path.
	 * 获取类路径的基名
     *
     * @return static
     */
    public function classBasename()
    {
        return new static(class_basename($this->value));
    }

    /**
     * Get the portion of a string before the first occurrence of a given value.
	 * 获取字符串第一次出现给定值之前的部分
     *
     * @param  string  $search
     * @return static
     */
    public function before($search)
    {
        return new static(Str::before($this->value, $search));
    }

    /**
     * Get the portion of a string before the last occurrence of a given value.
	 * 获取字符串最后一次出现给定值之前的部分
     *
     * @param  string  $search
     * @return static
     */
    public function beforeLast($search)
    {
        return new static(Str::beforeLast($this->value, $search));
    }

    /**
     * Get the portion of a string between two given values.
	 * 获取两个给定值之间的字符串部分
     *
     * @param  string  $from
     * @param  string  $to
     * @return static
     */
    public function between($from, $to)
    {
        return new static(Str::between($this->value, $from, $to));
    }

    /**
     * Convert a value to camel case.
	 * 将值转换为驼峰形式
     *
     * @return static
     */
    public function camel()
    {
        return new static(Str::camel($this->value));
    }

    /**
     * Determine if a given string contains a given substring.
	 * 确定给定字符串是否包含给定子字符串
     *
     * @param  string|array  $needles
     * @return bool
     */
    public function contains($needles)
    {
        return Str::contains($this->value, $needles);
    }

    /**
     * Determine if a given string contains all array values.
	 * 确定给定字符串是否包含所有数组值
     *
     * @param  array  $needles
     * @return bool
     */
    public function containsAll(array $needles)
    {
        return Str::containsAll($this->value, $needles);
    }

    /**
     * Get the parent directory's path.
	 * 获取父目录的路径
     *
     * @param  int  $levels
     * @return static
     */
    public function dirname($levels = 1)
    {
        return new static(dirname($this->value, $levels));
    }

    /**
     * Determine if a given string ends with a given substring.
	 * 确定给定字符串是否以给定子字符串结束
     *
     * @param  string|array  $needles
     * @return bool
     */
    public function endsWith($needles)
    {
        return Str::endsWith($this->value, $needles);
    }

    /**
     * Determine if the string is an exact match with the given value.
	 * 确定字符串是否与给定值完全匹配
     *
     * @param  string  $value
     * @return bool
     */
    public function exactly($value)
    {
        return $this->value === $value;
    }

    /**
     * Explode the string into an array.
	 * 将字符串爆炸成一个数组
     *
     * @param  string  $delimiter
     * @param  int  $limit
     * @return \Illuminate\Support\Collection
     */
    public function explode($delimiter, $limit = PHP_INT_MAX)
    {
        return collect(explode($delimiter, $this->value, $limit));
    }

    /**
     * Split a string using a regular expression or by length.
	 * 使用正则表达式或按长度拆分字符串
     *
     * @param  string|int  $pattern
     * @param  int  $limit
     * @param  int  $flags
     * @return \Illuminate\Support\Collection
     */
    public function split($pattern, $limit = -1, $flags = 0)
    {
        if (filter_var($pattern, FILTER_VALIDATE_INT) !== false) {
            return collect(mb_str_split($this->value, $pattern));
        }

        $segments = preg_split($pattern, $this->value, $limit, $flags);

        return ! empty($segments) ? collect($segments) : collect();
    }

    /**
     * Cap a string with a single instance of a given value.
	 * 用给定值的单个实例给字符串盖上盖子
     *
     * @param  string  $cap
     * @return static
     */
    public function finish($cap)
    {
        return new static(Str::finish($this->value, $cap));
    }

    /**
     * Determine if a given string matches a given pattern.
	 * 确定给定字符串是否与给定模式匹配
     *
     * @param  string|array  $pattern
     * @return bool
     */
    public function is($pattern)
    {
        return Str::is($pattern, $this->value);
    }

    /**
     * Determine if a given string is 7 bit ASCII.
	 * 确定给定字符串是否为7位ASCII
     *
     * @return bool
     */
    public function isAscii()
    {
        return Str::isAscii($this->value);
    }

    /**
     * Determine if a given string is a valid UUID.
	 * 确定给定字符串是否是有效的UUID
     *
     * @return bool
     */
    public function isUuid()
    {
        return Str::isUuid($this->value);
    }

    /**
     * Determine if the given string is empty.
	 * 确定给定字符串是否为空
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->value === '';
    }

    /**
     * Determine if the given string is not empty.
	 * 确定给定字符串是否为空
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return ! $this->isEmpty();
    }

    /**
     * Convert a string to kebab case.
	 * 将字符串转换为kebab case
     *
     * @return static
     */
    public function kebab()
    {
        return new static(Str::kebab($this->value));
    }

    /**
     * Return the length of the given string.
	 * 返回给定字符串的长度
     *
     * @param  string  $encoding
     * @return int
     */
    public function length($encoding = null)
    {
        return Str::length($this->value, $encoding);
    }

    /**
     * Limit the number of characters in a string.
	 * 限制字符串中的字符数
     *
     * @param  int  $limit
     * @param  string  $end
     * @return static
     */
    public function limit($limit = 100, $end = '...')
    {
        return new static(Str::limit($this->value, $limit, $end));
    }

    /**
     * Convert the given string to lower-case.
	 * 将给定的字符串转换为小写
     *
     * @return static
     */
    public function lower()
    {
        return new static(Str::lower($this->value));
    }

    /**
     * Convert GitHub flavored Markdown into HTML.
	 * 将GitHub风味的Markdown转换为HTML
     *
     * @param  array  $options
     * @return static
     */
    public function markdown(array $options = [])
    {
        return new static(Str::markdown($this->value, $options));
    }

    /**
     * Masks a portion of a string with a repeated character.
	 * 用重复字符掩码字符串的一部分
     *
     * @param  string  $character
     * @param  int  $index
     * @param  int|null  $length
     * @param  string  $encoding
     * @return static
     */
    public function mask($character, $index, $length = null, $encoding = 'UTF-8')
    {
        return new static(Str::mask($this->value, $character, $index, $length, $encoding));
    }

    /**
     * Get the string matching the given pattern.
	 * 获取与给定模式匹配的字符串
     *
     * @param  string  $pattern
     * @return static
     */
    public function match($pattern)
    {
        return new static(Str::match($pattern, $this->value));
    }

    /**
     * Get the string matching the given pattern.
	 * 获取与给定模式匹配的字符串
     *
     * @param  string  $pattern
     * @return \Illuminate\Support\Collection
     */
    public function matchAll($pattern)
    {
        return Str::matchAll($pattern, $this->value);
    }

    /**
     * Determine if the string matches the given pattern.
	 * 确定字符串是否与给定的模式匹配
     *
     * @param  string  $pattern
     * @return bool
     */
    public function test($pattern)
    {
        return $this->match($pattern)->isNotEmpty();
    }

    /**
     * Pad both sides of the string with another.
	 * 在绳子的两边垫上另一根
     *
     * @param  int  $length
     * @param  string  $pad
     * @return static
     */
    public function padBoth($length, $pad = ' ')
    {
        return new static(Str::padBoth($this->value, $length, $pad));
    }

    /**
     * Pad the left side of the string with another.
	 * 在绳子的左边垫上另一根
     *
     * @param  int  $length
     * @param  string  $pad
     * @return static
     */
    public function padLeft($length, $pad = ' ')
    {
        return new static(Str::padLeft($this->value, $length, $pad));
    }

    /**
     * Pad the right side of the string with another.
	 * 在绳子的右边垫上另一根
     *
     * @param  int  $length
     * @param  string  $pad
     * @return static
     */
    public function padRight($length, $pad = ' ')
    {
        return new static(Str::padRight($this->value, $length, $pad));
    }

    /**
     * Parse a Class@method style callback into class and method.
	 * 将Class@method样式的回调解析为类和方法
     *
     * @param  string|null  $default
     * @return array
     */
    public function parseCallback($default = null)
    {
        return Str::parseCallback($this->value, $default);
    }

    /**
     * Call the given callback and return a new string.
	 * 调用给定的回调函数并返回一个新字符串
     *
     * @param  callable  $callback
     * @return static
     */
    public function pipe(callable $callback)
    {
        return new static(call_user_func($callback, $this));
    }

    /**
     * Get the plural form of an English word.
	 * 了解英语单词的复数形式
     *
     * @param  int  $count
     * @return static
     */
    public function plural($count = 2)
    {
        return new static(Str::plural($this->value, $count));
    }

    /**
     * Pluralize the last word of an English, studly caps case string.
	 * 将英语的最后一个单词复数化，注意大小写字符串的大小写。
     *
     * @param  int  $count
     * @return static
     */
    public function pluralStudly($count = 2)
    {
        return new static(Str::pluralStudly($this->value, $count));
    }

    /**
     * Prepend the given values to the string.
	 * 将给定的值添加到字符串中
     *
     * @param  array  $values
     * @return static
     */
    public function prepend(...$values)
    {
        return new static(implode('', $values).$this->value);
    }

    /**
     * Remove any occurrence of the given string in the subject.
	 * 删除主题中出现的任何给定字符串
     *
     * @param  string|array<string>  $search
     * @param  bool  $caseSensitive
     * @return static
     */
    public function remove($search, $caseSensitive = true)
    {
        return new static(Str::remove($search, $this->value, $caseSensitive));
    }

    /**
     * Reverse the string.
	 * 颠倒字符串顺序
     *
     * @return static
     */
    public function reverse()
    {
        return new static(Str::reverse($this->value));
    }

    /**
     * Repeat the string.
	 * 重复字符串
     *
     * @param  int  $times
     * @return static
     */
    public function repeat(int $times)
    {
        return new static(Str::repeat($this->value, $times));
    }

    /**
     * Replace the given value in the given string.
	 * 替换给定字符串中的给定值
     *
     * @param  string|string[]  $search
     * @param  string|string[]  $replace
     * @return static
     */
    public function replace($search, $replace)
    {
        return new static(Str::replace($search, $replace, $this->value));
    }

    /**
     * Replace a given value in the string sequentially with an array.
	 * 将字符串中的给定值依次替换为数组
     *
     * @param  string  $search
     * @param  array  $replace
     * @return static
     */
    public function replaceArray($search, array $replace)
    {
        return new static(Str::replaceArray($search, $replace, $this->value));
    }

    /**
     * Replace the first occurrence of a given value in the string.
	 * 替换字符串中第一次出现的给定值
     *
     * @param  string  $search
     * @param  string  $replace
     * @return static
     */
    public function replaceFirst($search, $replace)
    {
        return new static(Str::replaceFirst($search, $replace, $this->value));
    }

    /**
     * Replace the last occurrence of a given value in the string.
	 * 替换字符串中最后出现的给定值
     *
     * @param  string  $search
     * @param  string  $replace
     * @return static
     */
    public function replaceLast($search, $replace)
    {
        return new static(Str::replaceLast($search, $replace, $this->value));
    }

    /**
     * Replace the patterns matching the given regular expression.
	 * 替换与给定正则表达式匹配的模式
     *
     * @param  string  $pattern
     * @param  \Closure|string  $replace
     * @param  int  $limit
     * @return static
     */
    public function replaceMatches($pattern, $replace, $limit = -1)
    {
        if ($replace instanceof Closure) {
            return new static(preg_replace_callback($pattern, $replace, $this->value, $limit));
        }

        return new static(preg_replace($pattern, $replace, $this->value, $limit));
    }

    /**
     * Parse input from a string to a collection, according to a format.
	 * 根据格式将输入从字符串解析为集合
     *
     * @param  string  $format
     * @return \Illuminate\Support\Collection
     */
    public function scan($format)
    {
        return collect(sscanf($this->value, $format));
    }

    /**
     * Begin a string with a single instance of a given value.
	 * 以给定值的单个实例开始字符串
     *
     * @param  string  $prefix
     * @return static
     */
    public function start($prefix)
    {
        return new static(Str::start($this->value, $prefix));
    }

    /**
     * Strip HTML and PHP tags from the given string.
	 * 从给定字符串中剥离HTML和PHP标记
     *
     * @param  string  $allowedTags
     * @return static
     */
    public function stripTags($allowedTags = null)
    {
        return new static(strip_tags($this->value, $allowedTags));
    }

    /**
     * Convert the given string to upper-case.
	 * 将给定的字符串转换为大写
     *
     * @return static
     */
    public function upper()
    {
        return new static(Str::upper($this->value));
    }

    /**
     * Convert the given string to title case.
	 * 将给定的字符串转换为标题大小写
     *
     * @return static
     */
    public function title()
    {
        return new static(Str::title($this->value));
    }

    /**
     * Convert the given string to title case for each word.
	 * 将给定字符串转换为每个单词的标题大小写
     *
     * @return static
     */
    public function headline()
    {
        return new static(Str::headline($this->value));
    }

    /**
     * Get the singular form of an English word.
	 * 获取英语单词的单数形式
     *
     * @return static
     */
    public function singular()
    {
        return new static(Str::singular($this->value));
    }

    /**
     * Generate a URL friendly "slug" from a given string.
	 * 从给定的字符串生成一个URL友好的"slug"
     *
     * @param  string  $separator
     * @param  string|null  $language
     * @return static
     */
    public function slug($separator = '-', $language = 'en')
    {
        return new static(Str::slug($this->value, $separator, $language));
    }

    /**
     * Convert a string to snake case.
	 * 将字符串转换为蛇形
     *
     * @param  string  $delimiter
     * @return static
     */
    public function snake($delimiter = '_')
    {
        return new static(Str::snake($this->value, $delimiter));
    }

    /**
     * Determine if a given string starts with a given substring.
	 * 确定给定字符串是否以给定子字符串开头
     *
     * @param  string|array  $needles
     * @return bool
     */
    public function startsWith($needles)
    {
        return Str::startsWith($this->value, $needles);
    }

    /**
     * Convert a value to studly caps case.
	 * 将值转换为大写大小写
     *
     * @return static
     */
    public function studly()
    {
        return new static(Str::studly($this->value));
    }

    /**
     * Returns the portion of the string specified by the start and length parameters.
	 * 返回由start和length参数指定的字符串部分
     *
     * @param  int  $start
     * @param  int|null  $length
     * @return static
     */
    public function substr($start, $length = null)
    {
        return new static(Str::substr($this->value, $start, $length));
    }

    /**
     * Returns the number of substring occurrences.
	 * 返回子字符串出现的次数
     *
     * @param  string  $needle
     * @param  int|null  $offset
     * @param  int|null  $length
     * @return int
     */
    public function substrCount($needle, $offset = null, $length = null)
    {
        return Str::substrCount($this->value, $needle, $offset ?? 0, $length);
    }

    /**
     * Replace text within a portion of a string.
	 * 替换字符串部分中的文本
     *
     * @param  string|array  $replace
     * @param  array|int  $offset
     * @param  array|int|null  $length
     * @return static
     */
    public function substrReplace($replace, $offset = 0, $length = null)
    {
        return new static(Str::substrReplace($this->value, $replace, $offset, $length));
    }

    /**
     * Swap multiple keywords in a string with other keywords.
	 * 将字符串中的多个关键字与其他关键字交换
     *
     * @param  array  $map
     * @return static
     */
    public function swap(array $map)
    {
        return new static(strtr($this->value, $map));
    }

    /**
     * Trim the string of the given characters.
	 * 修剪给定字符的字符串
     *
     * @param  string  $characters
     * @return static
     */
    public function trim($characters = null)
    {
        return new static(trim(...array_merge([$this->value], func_get_args())));
    }

    /**
     * Left trim the string of the given characters.
	 * 左剪给定字符的字符串
     *
     * @param  string  $characters
     * @return static
     */
    public function ltrim($characters = null)
    {
        return new static(ltrim(...array_merge([$this->value], func_get_args())));
    }

    /**
     * Right trim the string of the given characters.
	 * 右剪给定字符的字符串
     *
     * @param  string  $characters
     * @return static
     */
    public function rtrim($characters = null)
    {
        return new static(rtrim(...array_merge([$this->value], func_get_args())));
    }

    /**
     * Make a string's first character uppercase.
	 * 使字符串的第一个字符大写
     *
     * @return static
     */
    public function ucfirst()
    {
        return new static(Str::ucfirst($this->value));
    }

    /**
     * Split a string by uppercase characters.
	 * 按大写字符分割字符串
     *
     * @return \Illuminate\Support\Collection
     */
    public function ucsplit()
    {
        return collect(Str::ucsplit($this->value));
    }

    /**
     * Execute the given callback if the string contains a given substring.
	 * 如果字符串包含给定的子字符串，则执行给定的回调。
     *
     * @param  string|array  $needles
     * @param  callable  $callback
     * @param  callable|null  $default
     * @return static
     */
    public function whenContains($needles, $callback, $default = null)
    {
        return $this->when($this->contains($needles), $callback, $default);
    }

    /**
     * Execute the given callback if the string contains all array values.
	 * 如果字符串包含所有数组值，则执行给定的回调函数。
     *
     * @param  array  $needles
     * @param  callable  $callback
     * @param  callable|null  $default
     * @return static
     */
    public function whenContainsAll(array $needles, $callback, $default = null)
    {
        return $this->when($this->containsAll($needles), $callback, $default);
    }

    /**
     * Execute the given callback if the string is empty.
	 * 如果字符串为空，则执行给定的回调函数。
     *
     * @param  callable  $callback
     * @param  callable|null  $default
     * @return static
     */
    public function whenEmpty($callback, $default = null)
    {
        return $this->when($this->isEmpty(), $callback, $default);
    }

    /**
     * Execute the given callback if the string is not empty.
	 * 如果字符串不为空，则执行给定的回调函数。
     *
     * @param  callable  $callback
     * @param  callable|null  $default
     * @return static
     */
    public function whenNotEmpty($callback, $default = null)
    {
        return $this->when($this->isNotEmpty(), $callback, $default);
    }

    /**
     * Execute the given callback if the string ends with a given substring.
	 * 如果字符串以给定的子字符串结束，则执行给定的回调。
     *
     * @param  string|array  $needles
     * @param  callable  $callback
     * @param  callable|null  $default
     * @return static
     */
    public function whenEndsWith($needles, $callback, $default = null)
    {
        return $this->when($this->endsWith($needles), $callback, $default);
    }

    /**
     * Execute the given callback if the string is an exact match with the given value.
	 * 如果字符串与给定值完全匹配，则执行给定的回调函数。
     *
     * @param  string  $value
     * @param  callable  $callback
     * @param  callable|null  $default
     * @return static
     */
    public function whenExactly($value, $callback, $default = null)
    {
        return $this->when($this->exactly($value), $callback, $default);
    }

    /**
     * Execute the given callback if the string matches a given pattern.
	 * 如果字符串匹配给定的模式，则执行给定的回调。
     *
     * @param  string|array  $pattern
     * @param  callable  $callback
     * @param  callable|null  $default
     * @return static
     */
    public function whenIs($pattern, $callback, $default = null)
    {
        return $this->when($this->is($pattern), $callback, $default);
    }

    /**
     * Execute the given callback if the string is 7 bit ASCII.
	 * 如果字符串是7位ASCII，则执行给定的回调。
     *
     * @param  callable  $callback
     * @param  callable|null  $default
     * @return static
     */
    public function whenIsAscii($callback, $default = null)
    {
        return $this->when($this->isAscii(), $callback, $default);
    }

    /**
     * Execute the given callback if the string is a valid UUID.
	 * 如果字符串是有效的UUID，则执行给定的回调。
     *
     * @param  callable  $callback
     * @param  callable|null  $default
     * @return static
     */
    public function whenIsUuid($callback, $default = null)
    {
        return $this->when($this->isUuid(), $callback, $default);
    }

    /**
     * Execute the given callback if the string starts with a given substring.
	 * 如果字符串以给定的子字符串开头，则执行给定的回调。
     *
     * @param  string|array  $needles
     * @param  callable  $callback
     * @param  callable|null  $default
     * @return static
     */
    public function whenStartsWith($needles, $callback, $default = null)
    {
        return $this->when($this->startsWith($needles), $callback, $default);
    }

    /**
     * Execute the given callback if the string matches the given pattern.
	 * 如果字符串匹配给定的模式，则执行给定的回调。
     *
     * @param  string  $pattern
     * @param  callable  $callback
     * @param  callable|null  $default
     * @return static
     */
    public function whenTest($pattern, $callback, $default = null)
    {
        return $this->when($this->test($pattern), $callback, $default);
    }

    /**
     * Limit the number of words in a string.
	 * 限制字符串中的单词数
     *
     * @param  int  $words
     * @param  string  $end
     * @return static
     */
    public function words($words = 100, $end = '...')
    {
        return new static(Str::words($this->value, $words, $end));
    }

    /**
     * Get the number of words a string contains.
	 * 获取字符串包含的单词数
     *
     * @return int
     */
    public function wordCount()
    {
        return str_word_count($this->value);
    }

    /**
     * Convert the string into a `HtmlString` instance.
	 * 将字符串转换为'HtmlString'实例。
     *
     * @return \Illuminate\Support\HtmlString
     */
    public function toHtmlString()
    {
        return new HtmlString($this->value);
    }

    /**
     * Dump the string.
	 * 转储字符串
     *
     * @return $this
     */
    public function dump()
    {
        VarDumper::dump($this->value);

        return $this;
    }

    /**
     * Dump the string and end the script.
	 * 转储字符串并结束脚本
     *
     * @return never
     */
    public function dd()
    {
        $this->dump();

        exit(1);
    }

    /**
     * Convert the object to a string when JSON encoded.
	 * 在JSON编码时将对象转换为字符串
     *
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->__toString();
    }

    /**
     * Proxy dynamic properties onto methods.
	 * 将动态属性代理到方法上
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->{$key}();
    }

    /**
     * Get the raw string value.
	 * 获取原始字符串值
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }
}
