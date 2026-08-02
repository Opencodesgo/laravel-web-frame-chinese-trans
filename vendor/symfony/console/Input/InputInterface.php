<?php
/**
 * Symfony，Component，Console，输入，输入接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Input;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;

/**
 * InputInterface is the interface implemented by all input classes.
 * InputInterface是所有输入类实现的接口。
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface InputInterface
{
    /**
     * Returns the first argument from the raw parameters (not parsed).
	 * 从原始参数返回第一个参数(不解析)
     *
     * @return string|null
     */
    public function getFirstArgument();

    /**
     * Returns true if the raw parameters (not parsed) contain a value.
     *
     * This method is to be used to introspect the input parameters
     * before they have been validated. It must be used carefully.
     * Does not necessarily return the correct result for short options
     * when multiple flags are combined in the same option.
     *
     * @param string|array $values     The values to look for in the raw parameters (can be an array)
     * @param bool         $onlyParams Only check real parameters, skip those following an end of options (--) signal
     *
     * @return bool
     */
    public function hasParameterOption($values, bool $onlyParams = false);

    /**
     * Returns the value of a raw option (not parsed).
	 * 返回原始选项的值（未解析）。
     *
     * This method is to be used to introspect the input parameters
     * before they have been validated. It must be used carefully.
     * Does not necessarily return the correct result for short options
     * when multiple flags are combined in the same option.
     *
     * @param string|array                     $values     The value(s) to look for in the raw parameters (can be an array)
     * @param string|bool|int|float|array|null $default    The default value to return if no result is found
     * @param bool                             $onlyParams Only check real parameters, skip those following an end of options (--) signal
     *
     * @return mixed
     */
    public function getParameterOption($values, $default = false, bool $onlyParams = false);

    /**
     * Binds the current Input instance with the given arguments and options.
	 * 使用给定的参数和选项绑定当前输入实例
     *
     * @throws RuntimeException
     */
    public function bind(InputDefinition $definition);

    /**
     * Validates the input.
	 * 验证输入
     *
     * @throws RuntimeException When not enough arguments are given
     */
    public function validate();

    /**
     * Returns all the given arguments merged with the default values.
	 * 返回与默认值合并的所有给定参数
     *
     * @return array<string|bool|int|float|array|null>
     */
    public function getArguments();

    /**
     * Returns the argument value for a given argument name.
	 * 返回给定参数名称的参数值
     *
     * @return mixed
     *
     * @throws InvalidArgumentException When argument given doesn't exist
     */
    public function getArgument(string $name);

    /**
     * Sets an argument value by name.
	 * 按名称设置参数值
     *
     * @param mixed $value The argument value
     *
     * @throws InvalidArgumentException When argument given doesn't exist
     */
    public function setArgument(string $name, $value);

    /**
     * Returns true if an InputArgument object exists by name or position.
	 * 如果根据名称或位置存在inputarment对象，则返回true。
     *
     * @return bool
     */
    public function hasArgument(string $name);

    /**
     * Returns all the given options merged with the default values.
	 * 返回与默认值合并的所有给定选项
     *
     * @return array<string|bool|int|float|array|null>
     */
    public function getOptions();

    /**
     * Returns the option value for a given option name.
	 * 返回给定选项名称的选项值
     *
     * @return mixed
     *
     * @throws InvalidArgumentException When option given doesn't exist
     */
    public function getOption(string $name);

    /**
     * Sets an option value by name.
	 * 按名称设置选项值
     *
     * @param mixed $value The option value
     *
     * @throws InvalidArgumentException When option given doesn't exist
     */
    public function setOption(string $name, $value);

    /**
     * Returns true if an InputOption object exists by name.
	 * 如果按名称存在InputOption对象，则返回true。
     *
     * @return bool
     */
    public function hasOption(string $name);

    /**
     * Is this input means interactive?
	 * 这种输入是否意味着交互性
     *
     * @return bool
     */
    public function isInteractive();

    /**
     * Sets the input interactivity.
	 * 设置输入间活动
     */
    public function setInteractive(bool $interactive);
}
