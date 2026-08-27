<?php
/**
 * Symfony，Component，Console，问题，选择题
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Question;

use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * Represents a choice question.
 * 代表一个选择题。
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ChoiceQuestion extends Question
{
    private array $choices;
    private bool $multiselect = false;
    private string $prompt = ' > ';
    private string $errorMessage = 'Value "%s" is invalid';

    /**
     * @param string                     $question The question to ask to the user
     * @param array                      $choices  The list of available choices
     * @param string|bool|int|float|null $default  The default answer to return
     */
    public function __construct(string $question, array $choices, string|bool|int|float|null $default = null)
    {
        if (!$choices) {
            throw new \LogicException('Choice question must have at least 1 choice available.');
        }

        parent::__construct($question, $default);

        $this->choices = $choices;
        $this->setValidator($this->getDefaultValidator());
        $this->setAutocompleterValues($choices);
    }

    /**
     * Returns available choices.
	 * 返回可用的选项
     */
    public function getChoices(): array
    {
        return $this->choices;
    }

    /**
     * Sets multiselect option.
	 * 设置多选选项。
     *
     * When multiselect is set to true, multiple choices can be answered.
     *
     * @return $this
     */
    public function setMultiselect(bool $multiselect): static
    {
        $this->multiselect = $multiselect;
        $this->setValidator($this->getDefaultValidator());

        return $this;
    }

    /**
     * Returns whether the choices are multiselect.
	 * 返回选项是否为多选
     */
    public function isMultiselect(): bool
    {
        return $this->multiselect;
    }

    /**
     * Gets the prompt for choices.
	 * 获取选项的提示
     */
    public function getPrompt(): string
    {
        return $this->prompt;
    }

    /**
     * Sets the prompt for choices.
	 * 设置选项提示符
     *
     * @return $this
     */
    public function setPrompt(string $prompt): static
    {
        $this->prompt = $prompt;

        return $this;
    }

    /**
     * Sets the error message for invalid values.
	 * 为无效值设置错误消息。
     *
     * The error message has a string placeholder (%s) for the invalid value.
     *
     * @return $this
     */
    public function setErrorMessage(string $errorMessage): static
    {
        $this->errorMessage = $errorMessage;
        $this->setValidator($this->getDefaultValidator());

        return $this;
    }

    private function getDefaultValidator(): callable
    {
        $choices = $this->choices;
        $errorMessage = $this->errorMessage;
        $multiselect = $this->multiselect;
        $isAssoc = $this->isAssoc($choices);

        return function ($selected) use ($choices, $errorMessage, $multiselect, $isAssoc) {
            if ($multiselect) {
                // Check for a separated comma values
                if (!preg_match('/^[^,]+(?:,[^,]+)*$/', (string) $selected, $matches)) {
                    throw new InvalidArgumentException(\sprintf($errorMessage, $selected));
                }

                $selectedChoices = explode(',', (string) $selected);
            } else {
                $selectedChoices = [$selected];
            }

            if ($this->isTrimmable()) {
                foreach ($selectedChoices as $k => $v) {
                    $selectedChoices[$k] = trim((string) $v);
                }
            }

            $multiselectChoices = [];
            foreach ($selectedChoices as $value) {
                $results = [];
                foreach ($choices as $key => $choice) {
                    if ($choice === $value) {
                        $results[] = $key;
                    }
                }

                if (\count($results) > 1) {
                    throw new InvalidArgumentException(\sprintf('The provided answer is ambiguous. Value should be one of "%s".', implode('" or "', $results)));
                }

                $result = array_search($value, $choices);

                if (!$isAssoc) {
                    if (false !== $result) {
                        $result = $choices[$result];
                    } elseif (isset($choices[$value])) {
                        $result = $choices[$value];
                    }
                } elseif (false === $result && isset($choices[$value])) {
                    $result = $value;
                }

                if (false === $result) {
                    throw new InvalidArgumentException(\sprintf($errorMessage, $value));
                }

                // For associative choices, consistently return the key as string:
                $multiselectChoices[] = $isAssoc ? (string) $result : $result;
            }

            if ($multiselect) {
                return $multiselectChoices;
            }

            return current($multiselectChoices);
        };
    }
}
