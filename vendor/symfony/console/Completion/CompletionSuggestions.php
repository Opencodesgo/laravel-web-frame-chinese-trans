<?php
/**
 * Symfony，Component，Console，完成，完成建议
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Completion;

use Symfony\Component\Console\Input\InputOption;

/**
 * Stores all completion suggestions for the current input.
 * 存储当前输入的所有补全建议。
 *
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
final class CompletionSuggestions
{
    private array $valueSuggestions = [];
    private array $optionSuggestions = [];

    /**
     * Add a suggested value for an input option or argument.
	 * 为输入选项或参数添加建议值
     *
     * @return $this
     */
    public function suggestValue(string|Suggestion $value): static
    {
        $this->valueSuggestions[] = !$value instanceof Suggestion ? new Suggestion($value) : $value;

        return $this;
    }

    /**
     * Add multiple suggested values at once for an input option or argument.
	 * 一次为输入选项或参数添加多个建议值
     *
     * @param list<string|Suggestion> $values
     *
     * @return $this
     */
    public function suggestValues(array $values): static
    {
        foreach ($values as $value) {
            $this->suggestValue($value);
        }

        return $this;
    }

    /**
     * Add a suggestion for an input option name.
	 * 添加对输入选项名称的建议
     *
     * @return $this
     */
    public function suggestOption(InputOption $option): static
    {
        $this->optionSuggestions[] = $option;

        return $this;
    }

    /**
     * Add multiple suggestions for input option names at once.
	 * 一次为输入选项名添加多个建议
     *
     * @param InputOption[] $options
     *
     * @return $this
     */
    public function suggestOptions(array $options): static
    {
        foreach ($options as $option) {
            $this->suggestOption($option);
        }

        return $this;
    }

    /**
     * @return InputOption[]
     */
    public function getOptionSuggestions(): array
    {
        return $this->optionSuggestions;
    }

    /**
     * @return Suggestion[]
     */
    public function getValueSuggestions(): array
    {
        return $this->valueSuggestions;
    }
}
