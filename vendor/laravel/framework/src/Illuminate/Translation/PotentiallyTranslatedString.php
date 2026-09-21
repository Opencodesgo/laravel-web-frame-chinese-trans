<?php
/**
 * Illuminate, 翻译, 可能翻译的字符串
 */

namespace Illuminate\Translation;

use Stringable;

class PotentiallyTranslatedString implements Stringable
{
    /**
     * The string that may be translated.
	 * 可以被翻译的字符串
     *
     * @var string
     */
    protected $string;

    /**
     * The translated string.
	 * 翻译后的字符串
     *
     * @var string|null
     */
    protected $translation;

    /**
     * The validator that may perform the translation.
	 * 可能执行转换的验证器
     *
     * @var \Illuminate\Contracts\Translation\Translator
     */
    protected $translator;

    /**
     * Create a new potentially translated string.
	 * 创建一个新的可能已翻译的字符串
     *
     * @param  string  $string
     * @param  \Illuminate\Contracts\Translation\Translator  $translator
     */
    public function __construct($string, $translator)
    {
        $this->string = $string;

        $this->translator = $translator;
    }

    /**
     * Translate the string.
	 * 翻译字符串
     *
     * @param  array  $replace
     * @param  string|null  $locale
     * @return $this
     */
    public function translate($replace = [], $locale = null)
    {
        $this->translation = $this->translator->get($this->string, $replace, $locale);

        return $this;
    }

    /**
     * Translates the string based on a count.
	 * 根据计数转换字符串
     *
     * @param  \Countable|int|array  $number
     * @param  array  $replace
     * @param  string|null  $locale
     * @return $this
     */
    public function translateChoice($number, array $replace = [], $locale = null)
    {
        $this->translation = $this->translator->choice($this->string, $number, $replace, $locale);

        return $this;
    }

    /**
     * Get the original string.
	 * 获取原始字符串
     *
     * @return string
     */
    public function original()
    {
        return $this->string;
    }

    /**
     * Get the potentially translated string.
	 * 获取可能已翻译的字符串
     *
     * @return string
     */
    public function __toString()
    {
        return $this->translation ?? $this->string;
    }

    /**
     * Get the potentially translated string.
	 * 获取可能已翻译的字符串
     *
     * @return string
     */
    public function toString()
    {
        return (string) $this;
    }
}
