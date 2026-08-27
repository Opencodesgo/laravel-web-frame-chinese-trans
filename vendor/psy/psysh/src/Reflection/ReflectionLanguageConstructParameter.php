<?php
/**
 * Psy，反射，反射语言构造参数
 */

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2023 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\Reflection;

/**
 * A fake ReflectionParameter but for language construct parameters.
 * 一个假的ReflectionParameter，但用于语言构造参数。
 *
 * It stubs out all the important bits and returns whatever was passed in $opts.
 */
class ReflectionLanguageConstructParameter extends \ReflectionParameter
{
    /** @var string|array|object */
    private $function;
    /** @var int|string */
    private $parameter;
    private array $opts;

    /**
     * @param string|array|object $function
     * @param int|string          $parameter
     * @param array               $opts
     */
    public function __construct($function, $parameter, array $opts)
    {
        $this->function = $function;
        $this->parameter = $parameter;
        $this->opts = $opts;
    }

    /**
     * No class here.
	 * 这里没有课
     */
    public function getClass(): ?\ReflectionClass
    {
        return null;
    }

    /**
     * Is the param an array?
	 * 参数是一个数组吗？
     *
     * @return bool
     */
    public function isArray(): bool
    {
        return \array_key_exists('isArray', $this->opts) && $this->opts['isArray'];
    }

    /**
     * Get param default value.
	 * 获取参数的默认值
     *
     * @todo remove \ReturnTypeWillChange attribute after dropping support for PHP 7.x (when we can use mixed type)
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function getDefaultValue()
    {
        if ($this->isDefaultValueAvailable()) {
            return $this->opts['defaultValue'];
        }

        return null;
    }

    /**
     * Get param name.
	 * 获取参数名
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->parameter;
    }

    /**
     * Is the param optional?
	 * 参数是可选的吗？
     *
     * @return bool
     */
    public function isOptional(): bool
    {
        return \array_key_exists('isOptional', $this->opts) && $this->opts['isOptional'];
    }

    /**
     * Does the param have a default value?
	 * 参数是否有默认值
     *
     * @return bool
     */
    public function isDefaultValueAvailable(): bool
    {
        return \array_key_exists('defaultValue', $this->opts);
    }

    /**
     * Is the param passed by reference?
	 * 参数是通过引用传递的吗
     *
     * (I don't think this is true for anything we need to fake a param for)
     *
     * @return bool
     */
    public function isPassedByReference(): bool
    {
        return \array_key_exists('isPassedByReference', $this->opts) && $this->opts['isPassedByReference'];
    }
}
