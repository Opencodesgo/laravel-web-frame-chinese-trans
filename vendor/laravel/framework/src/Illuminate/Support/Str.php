<?php
/**
 * Illuminate，支持，字符串
 */

namespace Illuminate\Support;

use Illuminate\Support\Traits\Macroable;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Ramsey\Uuid\Codec\TimestampFirstCombCodec;
use Ramsey\Uuid\Generator\CombGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use voku\helper\ASCII;

class Str
{
    use Macroable;

    /**
     * The cache of snake-cased words.
	 * 蛇形大小写单词的缓存
     *
     * @var array
     */
    protected static $snakeCache = [];

    /**
     * The cache of camel-cased words.
	 * 驼峰式大小写单词的缓存
     *
     * @var array
     */
    protected static $camelCache = [];

    /**
     * The cache of studly-cased words.
	 * 隐藏的刻意区分大小写的单词
     *
     * @var array
     */
    protected static $studlyCache = [];

    /**
     * The callback that should be used to generate UUIDs.
	 * 应该用于生成uid的回调
     *
     * @var callable
     */
    protected static $uuidFactory;

    /**
     * Get a new stringable object from the given string.
	 * 从给定字符串中获取一个新的stringable对象
     *
     * @param  string  $string
     * @return \Illuminate\Support\Stringable
     */
    public static function of($string)
    {
        return new Stringable($string);
    }

    /**
     * Return the remainder of a string after the first occurrence of a given value.
	 * 返回给定值第一次出现后字符串的剩余部分
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    public static function after($subject, $search)
    {
        return $search === '' ? $subject : array_reverse(explode($search, $subject, 2))[0];
    }

    /**
     * Return the remainder of a string after the last occurrence of a given value.
	 * 返回给定值最后一次出现后字符串的剩余部分
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    public static function afterLast($subject, $search)
    {
        if ($search === '') {
            return $subject;
        }

        $position = strrpos($subject, (string) $search);

        if ($position === false) {
            return $subject;
        }

        return substr($subject, $position + strlen($search));
    }

    /**
     * Transliterate a UTF-8 value to ASCII.
	 * 将UTF-8值音译为ASCII
     *
     * @param  string  $value
     * @param  string  $language
     * @return string
     */
    public static function ascii($value, $language = 'en')
    {
        return ASCII::to_ascii((string) $value, $language);
    }

    /**
     * Transliterate a string to its closest ASCII representation.
	 * 将字符串音译为最接近的ASCII表示形式
     *
     * @param  string  $string
     * @param  string|null  $unknown
     * @param  bool|null  $strict
     * @return string
     */
    public static function transliterate($string, $unknown = '?', $strict = false)
    {
        return ASCII::to_transliterate($string, $unknown, $strict);
    }

    /**
     * Get the portion of a string before the first occurrence of a given value.
	 * 获取字符串第一次出现给定值之前的部分
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    public static function before($subject, $search)
    {
        if ($search === '') {
            return $subject;
        }

        $result = strstr($subject, (string) $search, true);

        return $result === false ? $subject : $result;
    }

    /**
     * Get the portion of a string before the last occurrence of a given value.
	 * 获取字符串最后一次出现给定值之前的部分
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    public static function beforeLast($subject, $search)
    {
        if ($search === '') {
            return $subject;
        }

        $pos = mb_strrpos($subject, $search);

        if ($pos === false) {
            return $subject;
        }

        return static::substr($subject, 0, $pos);
    }

    /**
     * Get the portion of a string between two given values.
	 * 获取两个给定值之间的字符串部分
     *
     * @param  string  $subject
     * @param  string  $from
     * @param  string  $to
     * @return string
     */
    public static function between($subject, $from, $to)
    {
        if ($from === '' || $to === '') {
            return $subject;
        }

        return static::beforeLast(static::after($subject, $from), $to);
    }

    /**
     * Convert a value to camel case.
	 * 将值转换为驼峰形式
     *
     * @param  string  $value
     * @return string
     */
    public static function camel($value)
    {
        if (isset(static::$camelCache[$value])) {
            return static::$camelCache[$value];
        }

        return static::$camelCache[$value] = lcfirst(static::studly($value));
    }

    /**
     * Determine if a given string contains a given substring.
	 * 确定给定字符串是否包含给定子字符串
     *
     * @param  string  $haystack
     * @param  string|string[]  $needles
     * @return bool
     */
    public static function contains($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string contains all array values.
	 * 确定给定字符串是否包含所有数组值
     *
     * @param  string  $haystack
     * @param  string[]  $needles
     * @return bool
     */
    public static function containsAll($haystack, array $needles)
    {
        foreach ($needles as $needle) {
            if (! static::contains($haystack, $needle)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if a given string ends with a given substring.
	 * 确定给定字符串是否以给定子字符串结束
     *
     * @param  string  $haystack
     * @param  string|string[]  $needles
     * @return bool
     */
    public static function endsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if (
                $needle !== '' && $needle !== null
                && substr($haystack, -strlen($needle)) === (string) $needle
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cap a string with a single instance of a given value.
	 * 用给定值的单个实例给字符串盖上盖子
     *
     * @param  string  $value
     * @param  string  $cap
     * @return string
     */
    public static function finish($value, $cap)
    {
        $quoted = preg_quote($cap, '/');

        return preg_replace('/(?:'.$quoted.')+$/u', '', $value).$cap;
    }

    /**
     * Determine if a given string matches a given pattern.
	 * 确定给定字符串是否与给定模式匹配
     *
     * @param  string|array  $pattern
     * @param  string  $value
     * @return bool
     */
    public static function is($pattern, $value)
    {
        $patterns = Arr::wrap($pattern);

        $value = (string) $value;

        if (empty($patterns)) {
            return false;
        }

        foreach ($patterns as $pattern) {
            $pattern = (string) $pattern;

            // If the given value is an exact match we can of course return true right
            // from the beginning. Otherwise, we will translate asterisks and do an
            // actual pattern match against the two strings to see if they match.
			// 如果给定的值是精确匹配的，我们当然可以返回true。
            if ($pattern == $value) {
                return true;
            }

            $pattern = preg_quote($pattern, '#');

            // Asterisks are translated into zero-or-more regular expression wildcards
            // to make it convenient to check if the strings starts with the given
            // pattern such as "library/*", making any string check convenient.
			// 星号被翻译成零或更多的正则表达式。
            $pattern = str_replace('\*', '.*', $pattern);

            if (preg_match('#^'.$pattern.'\z#u', $value) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string is 7 bit ASCII.
	 * 确定给定字符串是否为7位ASCII
     *
     * @param  string  $value
     * @return bool
     */
    public static function isAscii($value)
    {
        return ASCII::is_ascii((string) $value);
    }

    /**
     * Determine if a given string is a valid UUID.
	 * 确定给定字符串是否是有效的UUID
     *
     * @param  string  $value
     * @return bool
     */
    public static function isUuid($value)
    {
        if (! is_string($value)) {
            return false;
        }

        return preg_match('/^[\da-f]{8}-[\da-f]{4}-[\da-f]{4}-[\da-f]{4}-[\da-f]{12}$/iD', $value) > 0;
    }

    /**
     * Convert a string to kebab case.
	 * 将字符串转换为kebab case
     *
     * @param  string  $value
     * @return string
     */
    public static function kebab($value)
    {
        return static::snake($value, '-');
    }

    /**
     * Return the length of the given string.
	 * 返回给定字符串的长度
     *
     * @param  string  $value
     * @param  string|null  $encoding
     * @return int
     */
    public static function length($value, $encoding = null)
    {
        if ($encoding) {
            return mb_strlen($value, $encoding);
        }

        return mb_strlen($value);
    }

    /**
     * Limit the number of characters in a string.
	 * 限制字符串中的字符数
     *
     * @param  string  $value
     * @param  int  $limit
     * @param  string  $end
     * @return string
     */
    public static function limit($value, $limit = 100, $end = '...')
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')).$end;
    }

    /**
     * Convert the given string to lower-case.
	 * 将给定的字符串转换为小写
     *
     * @param  string  $value
     * @return string
     */
    public static function lower($value)
    {
        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * Limit the number of words in a string.
	 * 限制字符串中的单词数
     *
     * @param  string  $value
     * @param  int  $words
     * @param  string  $end
     * @return string
     */
    public static function words($value, $words = 100, $end = '...')
    {
        preg_match('/^\s*+(?:\S++\s*+){1,'.$words.'}/u', $value, $matches);

        if (! isset($matches[0]) || static::length($value) === static::length($matches[0])) {
            return $value;
        }

        return rtrim($matches[0]).$end;
    }

    /**
     * Converts GitHub flavored Markdown into HTML.
	 * 将GitHub风味的Markdown转换为HTML
     *
     * @param  string  $string
     * @param  array  $options
     * @return string
     */
    public static function markdown($string, array $options = [])
    {
        $converter = new GithubFlavoredMarkdownConverter($options);

        return (string) $converter->convertToHtml($string);
    }

    /**
     * Masks a portion of a string with a repeated character.
	 * 用重复字符掩码字符串的一部分
     *
     * @param  string  $string
     * @param  string  $character
     * @param  int  $index
     * @param  int|null  $length
     * @param  string  $encoding
     * @return string
     */
    public static function mask($string, $character, $index, $length = null, $encoding = 'UTF-8')
    {
        if ($character === '') {
            return $string;
        }

        if (is_null($length) && PHP_MAJOR_VERSION < 8) {
            $length = mb_strlen($string, $encoding);
        }

        $segment = mb_substr($string, $index, $length, $encoding);

        if ($segment === '') {
            return $string;
        }

        $strlen = mb_strlen($string, $encoding);
        $startIndex = $index;

        if ($index < 0) {
            $startIndex = $index < -$strlen ? 0 : $strlen + $index;
        }

        $start = mb_substr($string, 0, $startIndex, $encoding);
        $segmentLen = mb_strlen($segment, $encoding);
        $end = mb_substr($string, $startIndex + $segmentLen);

        return $start.str_repeat(mb_substr($character, 0, 1, $encoding), $segmentLen).$end;
    }

    /**
     * Get the string matching the given pattern.
	 * 获取与给定模式匹配的字符串
     *
     * @param  string  $pattern
     * @param  string  $subject
     * @return string
     */
    public static function match($pattern, $subject)
    {
        preg_match($pattern, $subject, $matches);

        if (! $matches) {
            return '';
        }

        return $matches[1] ?? $matches[0];
    }

    /**
     * Get the string matching the given pattern.
	 * 获取与给定模式匹配的字符串
     *
     * @param  string  $pattern
     * @param  string  $subject
     * @return \Illuminate\Support\Collection
     */
    public static function matchAll($pattern, $subject)
    {
        preg_match_all($pattern, $subject, $matches);

        if (empty($matches[0])) {
            return collect();
        }

        return collect($matches[1] ?? $matches[0]);
    }

    /**
     * Pad both sides of a string with another.
	 * 在绳子的两边垫上另一根
     *
     * @param  string  $value
     * @param  int  $length
     * @param  string  $pad
     * @return string
     */
    public static function padBoth($value, $length, $pad = ' ')
    {
        return str_pad($value, strlen($value) - mb_strlen($value) + $length, $pad, STR_PAD_BOTH);
    }

    /**
     * Pad the left side of a string with another.
	 * 用另一根绳子填充绳子的左边
     *
     * @param  string  $value
     * @param  int  $length
     * @param  string  $pad
     * @return string
     */
    public static function padLeft($value, $length, $pad = ' ')
    {
        return str_pad($value, strlen($value) - mb_strlen($value) + $length, $pad, STR_PAD_LEFT);
    }

    /**
     * Pad the right side of a string with another.
	 * 用另一根绳子填满绳子的右侧
     *
     * @param  string  $value
     * @param  int  $length
     * @param  string  $pad
     * @return string
     */
    public static function padRight($value, $length, $pad = ' ')
    {
        return str_pad($value, strlen($value) - mb_strlen($value) + $length, $pad, STR_PAD_RIGHT);
    }

    /**
     * Parse a Class[@]method style callback into class and method.
	 * 解析类[@]方法风格回调到类和方法
     *
     * @param  string  $callback
     * @param  string|null  $default
     * @return array<int, string|null>
     */
    public static function parseCallback($callback, $default = null)
    {
        return static::contains($callback, '@') ? explode('@', $callback, 2) : [$callback, $default];
    }

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
        return Pluralizer::plural($value, $count);
    }

    /**
     * Pluralize the last word of an English, studly caps case string.
	 * 将英语的最后一个单词复数化，注意大小写字符串的大小写。
     *
     * @param  string  $value
     * @param  int|array|\Countable  $count
     * @return string
     */
    public static function pluralStudly($value, $count = 2)
    {
        $parts = preg_split('/(.)(?=[A-Z])/u', $value, -1, PREG_SPLIT_DELIM_CAPTURE);

        $lastWord = array_pop($parts);

        return implode('', $parts).self::plural($lastWord, $count);
    }

    /**
     * Generate a more truly "random" alpha-numeric string.
	 * 生成一个更真正“随机”的字母数字字符串
     *
     * @param  int  $length
     * @return string
     */
    public static function random($length = 16)
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    /**
     * Repeat the given string.
	 * 重复给定的字符串
     *
     * @param  string  $string
     * @param  int  $times
     * @return string
     */
    public static function repeat(string $string, int $times)
    {
        return str_repeat($string, $times);
    }

    /**
     * Replace a given value in the string sequentially with an array.
	 * 将字符串中的给定值依次替换为数组
     *
     * @param  string  $search
     * @param  array<int|string, string>  $replace
     * @param  string  $subject
     * @return string
     */
    public static function replaceArray($search, array $replace, $subject)
    {
        $segments = explode($search, $subject);

        $result = array_shift($segments);

        foreach ($segments as $segment) {
            $result .= (array_shift($replace) ?? $search).$segment;
        }

        return $result;
    }

    /**
     * Replace the given value in the given string.
	 * 替换给定字符串中的给定值
     *
     * @param  string|string[]  $search
     * @param  string|string[]  $replace
     * @param  string|string[]  $subject
     * @return string
     */
    public static function replace($search, $replace, $subject)
    {
        return str_replace($search, $replace, $subject);
    }

    /**
     * Replace the first occurrence of a given value in the string.
	 * 替换字符串中第一次出现的给定值
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $subject
     * @return string
     */
    public static function replaceFirst($search, $replace, $subject)
    {
        if ($search === '') {
            return $subject;
        }

        $position = strpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }

    /**
     * Replace the last occurrence of a given value in the string.
	 * 替换字符串中最后出现的给定值
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $subject
     * @return string
     */
    public static function replaceLast($search, $replace, $subject)
    {
        if ($search === '') {
            return $subject;
        }

        $position = strrpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }

    /**
     * Remove any occurrence of the given string in the subject.
	 * 删除主题中出现的任何给定字符串
     *
     * @param  string|array<string>  $search
     * @param  string  $subject
     * @param  bool  $caseSensitive
     * @return string
     */
    public static function remove($search, $subject, $caseSensitive = true)
    {
        $subject = $caseSensitive
                    ? str_replace($search, '', $subject)
                    : str_ireplace($search, '', $subject);

        return $subject;
    }

    /**
     * Reverse the given string.
	 * 反转给定的字符串
     *
     * @param  string  $value
     * @return string
     */
    public static function reverse(string $value)
    {
        return implode(array_reverse(mb_str_split($value)));
    }

    /**
     * Begin a string with a single instance of a given value.
	 * 以给定值的单个实例开始字符串
     *
     * @param  string  $value
     * @param  string  $prefix
     * @return string
     */
    public static function start($value, $prefix)
    {
        $quoted = preg_quote($prefix, '/');

        return $prefix.preg_replace('/^(?:'.$quoted.')+/u', '', $value);
    }

    /**
     * Convert the given string to upper-case.
	 * 将给定的字符串转换为大写
     *
     * @param  string  $value
     * @return string
     */
    public static function upper($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    /**
     * Convert the given string to title case.
	 * 将给定的字符串转换为标题大小写
     *
     * @param  string  $value
     * @return string
     */
    public static function title($value)
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Convert the given string to title case for each word.
	 * 将给定字符串转换为每个单词的标题大小写
     *
     * @param  string  $value
     * @return string
     */
    public static function headline($value)
    {
        $parts = explode(' ', $value);

        $parts = count($parts) > 1
            ? $parts = array_map([static::class, 'title'], $parts)
            : $parts = array_map([static::class, 'title'], static::ucsplit(implode('_', $parts)));

        $collapsed = static::replace(['-', '_', ' '], '_', implode('_', $parts));

        return implode(' ', array_filter(explode('_', $collapsed)));
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
        return Pluralizer::singular($value);
    }

    /**
     * Generate a URL friendly "slug" from a given string.
	 * 从给定的字符串生成一个URL友好的"slug"
     *
     * @param  string  $title
     * @param  string  $separator
     * @param  string|null  $language
     * @return string
     */
    public static function slug($title, $separator = '-', $language = 'en')
    {
        $title = $language ? static::ascii($title, $language) : $title;

        // Convert all dashes/underscores into separator
		// 将所有破折号/下划线转换为分隔符
        $flip = $separator === '-' ? '_' : '-';

        $title = preg_replace('!['.preg_quote($flip).']+!u', $separator, $title);

        // Replace @ with the word 'at'
		// 用单词'at'替换@
        $title = str_replace('@', $separator.'at'.$separator, $title);

        // Remove all characters that are not the separator, letters, numbers, or whitespace.
		// 删除除分隔符、字母、数字或空格以外的所有字符。
        $title = preg_replace('![^'.preg_quote($separator).'\pL\pN\s]+!u', '', static::lower($title));

        // Replace all separator characters and whitespace by a single separator
		// 用单个分隔符替换所有分隔符和空格
        $title = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $title);

        return trim($title, $separator);
    }

    /**
     * Convert a string to snake case.
	 * 将字符串转换为蛇形
     *
     * @param  string  $value
     * @param  string  $delimiter
     * @return string
     */
    public static function snake($value, $delimiter = '_')
    {
        $key = $value;

        if (isset(static::$snakeCache[$key][$delimiter])) {
            return static::$snakeCache[$key][$delimiter];
        }

        if (! ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));

            $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
        }

        return static::$snakeCache[$key][$delimiter] = $value;
    }

    /**
     * Determine if a given string starts with a given substring.
	 * 确定给定字符串是否以给定子字符串开头
     *
     * @param  string  $haystack
     * @param  string|string[]  $needles
     * @return bool
     */
    public static function startsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ((string) $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert a value to studly caps case.
	 * 将值转换为大写大小写
     *
     * @param  string  $value
     * @return string
     */
    public static function studly($value)
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $words = explode(' ', static::replace(['-', '_'], ' ', $value));

        $studlyWords = array_map(function ($word) {
            return static::ucfirst($word);
        }, $words);

        return static::$studlyCache[$key] = implode($studlyWords);
    }

    /**
     * Returns the portion of the string specified by the start and length parameters.
	 * 返回由start和length参数指定的字符串部分
     *
     * @param  string  $string
     * @param  int  $start
     * @param  int|null  $length
     * @return string
     */
    public static function substr($string, $start, $length = null)
    {
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    /**
     * Returns the number of substring occurrences.
	 * 返回子字符串出现的次数
     *
     * @param  string  $haystack
     * @param  string  $needle
     * @param  int  $offset
     * @param  int|null  $length
     * @return int
     */
    public static function substrCount($haystack, $needle, $offset = 0, $length = null)
    {
        if (! is_null($length)) {
            return substr_count($haystack, $needle, $offset, $length);
        } else {
            return substr_count($haystack, $needle, $offset);
        }
    }

    /**
     * Replace text within a portion of a string.
	 * 替换字符串部分中的文本
     *
     * @param  string|array  $string
     * @param  string|array  $replace
     * @param  array|int  $offset
     * @param  array|int|null  $length
     * @return string|array
     */
    public static function substrReplace($string, $replace, $offset = 0, $length = null)
    {
        if ($length === null) {
            $length = strlen($string);
        }

        return substr_replace($string, $replace, $offset, $length);
    }

    /**
     * Swap multiple keywords in a string with other keywords.
	 * 将字符串中的多个关键字与其他关键字交换
     *
     * @param  array  $map
     * @param  string  $subject
     * @return string
     */
    public static function swap(array $map, $subject)
    {
        return strtr($subject, $map);
    }

    /**
     * Make a string's first character uppercase.
	 * 使字符串的第一个字符大写
     *
     * @param  string  $string
     * @return string
     */
    public static function ucfirst($string)
    {
        return static::upper(static::substr($string, 0, 1)).static::substr($string, 1);
    }

    /**
     * Split a string into pieces by uppercase characters.
	 * 用大写字符将字符串分成几个部分
     *
     * @param  string  $string
     * @return array
     */
    public static function ucsplit($string)
    {
        return preg_split('/(?=\p{Lu})/u', $string, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Get the number of words a string contains.
	 * 获取字符串包含的单词数
     *
     * @param  string  $string
     * @return int
     */
    public static function wordCount($string)
    {
        return str_word_count($string);
    }

    /**
     * Generate a UUID (version 4).
	 * 生成UUID（版本4）
     *
     * @return \Ramsey\Uuid\UuidInterface
     */
    public static function uuid()
    {
        return static::$uuidFactory
                    ? call_user_func(static::$uuidFactory)
                    : Uuid::uuid4();
    }

    /**
     * Generate a time-ordered UUID (version 4).
	 * 生成一个按时间排序的UUID（版本4）
     *
     * @return \Ramsey\Uuid\UuidInterface
     */
    public static function orderedUuid()
    {
        if (static::$uuidFactory) {
            return call_user_func(static::$uuidFactory);
        }

        $factory = new UuidFactory;

        $factory->setRandomGenerator(new CombGenerator(
            $factory->getRandomGenerator(),
            $factory->getNumberConverter()
        ));

        $factory->setCodec(new TimestampFirstCombCodec(
            $factory->getUuidBuilder()
        ));

        return $factory->uuid4();
    }

    /**
     * Set the callable that will be used to generate UUIDs.
	 * 设置将用于生成uid的可调用对象
     *
     * @param  callable|null  $factory
     * @return void
     */
    public static function createUuidsUsing(callable $factory = null)
    {
        static::$uuidFactory = $factory;
    }

    /**
     * Indicate that UUIDs should be created normally and not using a custom factory.
	 * 指示应该正常创建uid，而不是使用自定义工厂。
     *
     * @return void
     */
    public static function createUuidsNormally()
    {
        static::$uuidFactory = null;
    }

    /**
     * Remove all strings from the casing caches.
	 * 从套管缓存中删除所有字符串
     *
     * @return void
     */
    public static function flushCache()
    {
        static::$snakeCache = [];
        static::$camelCache = [];
        static::$studlyCache = [];
    }
}
