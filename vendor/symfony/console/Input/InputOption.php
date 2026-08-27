<?php
/**
 * Symfony，Component，Console，输入，输入选项
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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;
use Symfony\Component\Console\Completion\Suggestion;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;

/**
 * Represents a command line option.
 * 表示命令行选项。
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class InputOption
{
    /**
     * Do not accept input for the option (e.g. --yell). This is the default behavior of options.
	 * 不接受选项的输入（例如——yell）。这是选项的默认行为。
     */
    public const VALUE_NONE = 1;

    /**
     * A value must be passed when the option is used (e.g. --iterations=5 or -i5).
	 * 当使用该选项时，必须传递一个值。
     */
    public const VALUE_REQUIRED = 2;

    /**
     * The option may or may not have a value (e.g. --yell or --yell=loud).
     */
    public const VALUE_OPTIONAL = 4;

    /**
     * The option accepts multiple values (e.g. --dir=/foo --dir=/bar).
     */
    public const VALUE_IS_ARRAY = 8;

    /**
     * The option may have either positive or negative value (e.g. --ansi or --no-ansi).
     */
    public const VALUE_NEGATABLE = 16;

    private string $name;
    private string|array|null $shortcut;
    private int $mode;
    private string|int|bool|array|float|null $default;
    private array|\Closure $suggestedValues;
    private string $description;

    /**
     * @param string|array|null                                                             $shortcut        The shortcuts, can be null, a string of shortcuts delimited by | or an array of shortcuts
     * @param int|null                                                                      $mode            The option mode: One of the VALUE_* constants
     * @param string|bool|int|float|array|null                                              $default         The default value (must be null for self::VALUE_NONE)
     * @param array|\Closure(CompletionInput,CompletionSuggestions):list<string|Suggestion> $suggestedValues The values used for input completion
     *
     * @throws InvalidArgumentException If option mode is invalid or incompatible
     */
    public function __construct(string $name, string|array|null $shortcut = null, ?int $mode = null, string $description = '', string|bool|int|float|array|null $default = null, array|\Closure $suggestedValues = [])
    {
        if (str_starts_with($name, '--')) {
            $name = substr($name, 2);
        }

        if (empty($name)) {
            throw new InvalidArgumentException('An option name cannot be empty.');
        }

        if ('' === $shortcut || [] === $shortcut || false === $shortcut) {
            $shortcut = null;
        }

        if (null !== $shortcut) {
            if (\is_array($shortcut)) {
                $shortcut = implode('|', $shortcut);
            }
            $shortcuts = preg_split('{(\|)-?}', ltrim($shortcut, '-'));
            $shortcuts = array_filter($shortcuts, 'strlen');
            $shortcut = implode('|', $shortcuts);

            if ('' === $shortcut) {
                throw new InvalidArgumentException('An option shortcut cannot be empty.');
            }
        }

        if (null === $mode) {
            $mode = self::VALUE_NONE;
        } elseif ($mode >= (self::VALUE_NEGATABLE << 1) || $mode < 1) {
            throw new InvalidArgumentException(\sprintf('Option mode "%s" is not valid.', $mode));
        }

        $this->name = $name;
        $this->shortcut = $shortcut;
        $this->mode = $mode;
        $this->description = $description;
        $this->suggestedValues = $suggestedValues;

        if ($suggestedValues && !$this->acceptValue()) {
            throw new LogicException('Cannot set suggested values if the option does not accept a value.');
        }
        if ($this->isArray() && !$this->acceptValue()) {
            throw new InvalidArgumentException('Impossible to have an option mode VALUE_IS_ARRAY if the option does not accept a value.');
        }
        if ($this->isNegatable() && $this->acceptValue()) {
            throw new InvalidArgumentException('Impossible to have an option mode VALUE_NEGATABLE if the option also accepts a value.');
        }

        $this->setDefault($default);
    }

    /**
     * Returns the option shortcut.
	 * 返回选项快捷方式
     */
    public function getShortcut(): ?string
    {
        return $this->shortcut;
    }

    /**
     * Returns the option name.
	 * 返回选项名
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns true if the option accepts a value.
	 * 如果选项接受值，则返回true。
     *
     * @return bool true if value mode is not self::VALUE_NONE, false otherwise
     */
    public function acceptValue(): bool
    {
        return $this->isValueRequired() || $this->isValueOptional();
    }

    /**
     * Returns true if the option requires a value.
	 * 如果选项需要值，则返回true。
     *
     * @return bool true if value mode is self::VALUE_REQUIRED, false otherwise
     */
    public function isValueRequired(): bool
    {
        return self::VALUE_REQUIRED === (self::VALUE_REQUIRED & $this->mode);
    }

    /**
     * Returns true if the option takes an optional value.
	 * 如果该选项接受可选值，则返回true。
     *
     * @return bool true if value mode is self::VALUE_OPTIONAL, false otherwise
     */
    public function isValueOptional(): bool
    {
        return self::VALUE_OPTIONAL === (self::VALUE_OPTIONAL & $this->mode);
    }

    /**
     * Returns true if the option can take multiple values.
	 * 如果选项可以接受多个值，则返回true。
     *
     * @return bool true if mode is self::VALUE_IS_ARRAY, false otherwise
     */
    public function isArray(): bool
    {
        return self::VALUE_IS_ARRAY === (self::VALUE_IS_ARRAY & $this->mode);
    }

    public function isNegatable(): bool
    {
        return self::VALUE_NEGATABLE === (self::VALUE_NEGATABLE & $this->mode);
    }

    /**
     * @return void
     */
    public function setDefault(string|bool|int|float|array|null $default = null)
    {
        if (1 > \func_num_args()) {
            trigger_deprecation('symfony/console', '6.2', 'Calling "%s()" without any arguments is deprecated, pass null explicitly instead.', __METHOD__);
        }
        if (self::VALUE_NONE === (self::VALUE_NONE & $this->mode) && null !== $default) {
            throw new LogicException('Cannot set a default value when using InputOption::VALUE_NONE mode.');
        }

        if ($this->isArray()) {
            if (null === $default) {
                $default = [];
            } elseif (!\is_array($default)) {
                throw new LogicException('A default value for an array option must be an array.');
            }
        }

        $this->default = $this->acceptValue() || $this->isNegatable() ? $default : false;
    }

    /**
     * Returns the default value.
	 * 返回默认值
     */
    public function getDefault(): string|bool|int|float|array|null
    {
        return $this->default;
    }

    /**
     * Returns the description text.
	 * 返回描述文本
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    public function hasCompletion(): bool
    {
        return [] !== $this->suggestedValues;
    }

    /**
     * Adds suggestions to $suggestions for the current completion input.
	 * 将当前补全输入的建议添加到$suggestions中。
     *
     * @see Command::complete()
     */
    public function complete(CompletionInput $input, CompletionSuggestions $suggestions): void
    {
        $values = $this->suggestedValues;
        if ($values instanceof \Closure && !\is_array($values = $values($input))) {
            throw new LogicException(\sprintf('Closure for option "%s" must return an array. Got "%s".', $this->name, get_debug_type($values)));
        }
        if ($values) {
            $suggestions->suggestValues($values);
        }
    }

    /**
     * Checks whether the given option equals this one.
	 * 检查给定的选项是否等于这个选项
     */
    public function equals(self $option): bool
    {
        return $option->getName() === $this->getName()
            && $option->getShortcut() === $this->getShortcut()
            && $option->getDefault() === $this->getDefault()
            && $option->isNegatable() === $this->isNegatable()
            && $option->isArray() === $this->isArray()
            && $option->isValueRequired() === $this->isValueRequired()
            && $option->isValueOptional() === $this->isValueOptional()
        ;
    }
}
