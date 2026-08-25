<?php
/**
 * Psy，上下文
 */

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2023 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy;

/**
 * The Shell execution context.
 * Shell执行上下文。
 *
 * This class encapsulates the current variables, most recent return value and
 * exception, and the current namespace.
 * 该类封装了当前变量、最近的返回值和异常，以及当前命名空间。
 */
class Context
{
    private const SPECIAL_NAMES = ['_', '_e', '__out', '__psysh__', 'this'];

    // Include a very limited number of command-scope magic variable names.
    // This might be a bad idea, but future me can sort it out.
	// 包含非常有限数量的命令作用域魔法变量名称。这或许是个糟糕的想法，但未来的我能够解决这个问题。
    private const COMMAND_SCOPE_NAMES = [
        '__function', '__method', '__class', '__namespace', '__file', '__line', '__dir',
    ];

    private array $scopeVariables = [];
    private array $commandScopeVariables = [];
    /** @var mixed */
    private $returnValue = null;
    private ?\Throwable $lastException = null;
    private ?string $lastStdout = null;
    private ?object $boundObject = null;
    private ?string $boundClass = null;

    /**
     * Get a context variable.
	 * 获取一个上下文变量
     *
     * @throws \InvalidArgumentException If the variable is not found in the current context
     *
     * @return mixed
     */
    public function get(string $name)
    {
        switch ($name) {
            case '_':
                return $this->returnValue;

            case '_e':
                if (isset($this->lastException)) {
                    return $this->lastException;
                }
                break;

            case '__out':
                if (isset($this->lastStdout)) {
                    return $this->lastStdout;
                }
                break;

            case 'this':
                if (isset($this->boundObject)) {
                    return $this->boundObject;
                }
                break;

            case '__function':
            case '__method':
            case '__class':
            case '__namespace':
            case '__file':
            case '__line':
            case '__dir':
                if (\array_key_exists($name, $this->commandScopeVariables)) {
                    return $this->commandScopeVariables[$name];
                }
                break;

            default:
                if (\array_key_exists($name, $this->scopeVariables)) {
                    return $this->scopeVariables[$name];
                }
                break;
        }

        throw new \InvalidArgumentException('Unknown variable: $'.$name);
    }

    /**
     * Get all defined variables.
	 * 获取所有已定义的变量
     */
    public function getAll(): array
    {
        return \array_merge($this->scopeVariables, $this->getSpecialVariables());
    }

    /**
     * Get all defined magic variables: $_, $_e, $__out, $__class, $__file, etc.
     */
    public function getSpecialVariables(): array
    {
        $vars = [
            '_' => $this->returnValue,
        ];

        if (isset($this->lastException)) {
            $vars['_e'] = $this->lastException;
        }

        if (isset($this->lastStdout)) {
            $vars['__out'] = $this->lastStdout;
        }

        if (isset($this->boundObject)) {
            $vars['this'] = $this->boundObject;
        }

        return \array_merge($vars, $this->commandScopeVariables);
    }

    /**
     * Set all scope variables.
	 * 设置所有作用域变量
     *
     * This method does *not* set any of the magic variables: $_, $_e, $__out,
     * $__class, $__file, etc.
     */
    public function setAll(array $vars)
    {
        foreach (self::SPECIAL_NAMES as $key) {
            unset($vars[$key]);
        }

        foreach (self::COMMAND_SCOPE_NAMES as $key) {
            unset($vars[$key]);
        }

        $this->scopeVariables = $vars;
    }

    /**
     * Set the most recent return value.
	 * 设置最近的返回值
     *
     * @param mixed $value
     */
    public function setReturnValue($value)
    {
        $this->returnValue = $value;
    }

    /**
     * Get the most recent return value.
	 * 获取最近的返回值
     *
     * @return mixed
     */
    public function getReturnValue()
    {
        return $this->returnValue;
    }

    /**
     * Set the most recent Exception or Error.
	 * 设置最近的异常或错误
     *
     * @param \Throwable $e
     */
    public function setLastException(\Throwable $e)
    {
        $this->lastException = $e;
    }

    /**
     * Get the most recent Exception or Error.
	 * 获取最近的Exception或Error
     *
     * @throws \InvalidArgumentException If no Exception has been caught
     *
     * @return \Throwable|null
     */
    public function getLastException()
    {
        if (!isset($this->lastException)) {
            throw new \InvalidArgumentException('No most-recent exception');
        }

        return $this->lastException;
    }

    /**
     * Set the most recent output from evaluated code.
	 * 设置已计算代码的最新输出
     */
    public function setLastStdout(string $lastStdout)
    {
        $this->lastStdout = $lastStdout;
    }

    /**
     * Get the most recent output from evaluated code.
	 * 从求值的代码中获取最新的输出
     *
     * @throws \InvalidArgumentException If no output has happened yet
     *
     * @return string|null
     */
    public function getLastStdout()
    {
        if (!isset($this->lastStdout)) {
            throw new \InvalidArgumentException('No most-recent output');
        }

        return $this->lastStdout;
    }

    /**
     * Set the bound object ($this variable) for the interactive shell.
	 * 为交互式shell设置绑定对象（$this变量）。
     *
     * Note that this unsets the bound class, if any exists.
     *
     * @param object|null $boundObject
     */
    public function setBoundObject($boundObject)
    {
        $this->boundObject = \is_object($boundObject) ? $boundObject : null;
        $this->boundClass = null;
    }

    /**
     * Get the bound object ($this variable) for the interactive shell.
	 * 获取交互shell的绑定对象（$this变量）
     *
     * @return object|null
     */
    public function getBoundObject()
    {
        return $this->boundObject;
    }

    /**
     * Set the bound class (self) for the interactive shell.
	 * 为交互式shell设置绑定类（self）。
     *
     * Note that this unsets the bound object, if any exists.
     *
     * @param string|null $boundClass
     */
    public function setBoundClass($boundClass)
    {
        $this->boundClass = (\is_string($boundClass) && $boundClass !== '') ? $boundClass : null;
        $this->boundObject = null;
    }

    /**
     * Get the bound class (self) for the interactive shell.
	 * 获取交互式shell的绑定类（self）
     *
     * @return string|null
     */
    public function getBoundClass()
    {
        return $this->boundClass;
    }

    /**
     * Set command-scope magic variables: $__class, $__file, etc.
     */
    public function setCommandScopeVariables(array $commandScopeVariables)
    {
        $vars = [];
        foreach ($commandScopeVariables as $key => $value) {
            // kind of type check
            if (\is_scalar($value) && \in_array($key, self::COMMAND_SCOPE_NAMES)) {
                $vars[$key] = $value;
            }
        }

        $this->commandScopeVariables = $vars;
    }

    /**
     * Get command-scope magic variables: $__class, $__file, etc.
     */
    public function getCommandScopeVariables(): array
    {
        return $this->commandScopeVariables;
    }

    /**
     * Get unused command-scope magic variables names: __class, __file, etc.
     *
     * This is used by the shell to unset old command-scope variables after a
     * new batch is set.
     *
     * @return array Array of unused variable names
     */
    public function getUnusedCommandScopeVariableNames(): array
    {
        return \array_diff(self::COMMAND_SCOPE_NAMES, \array_keys($this->commandScopeVariables));
    }

    /**
     * Check whether a variable name is a magic variable.
	 * 检查变量名是否为幻变量
     */
    public static function isSpecialVariableName(string $name): bool
    {
        return \in_array($name, self::SPECIAL_NAMES) || \in_array($name, self::COMMAND_SCOPE_NAMES);
    }
}
