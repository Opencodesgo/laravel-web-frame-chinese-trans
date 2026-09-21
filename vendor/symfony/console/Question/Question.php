<?php
/**
 * Symfony，Component，Console，问题，问题
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
use Symfony\Component\Console\Exception\LogicException;

/**
 * Represents a Question.
 * 代表一个问题。
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Question
{
    private string $question;
    private ?int $attempts = null;
    private bool $hidden = false;
    private bool $hiddenFallback = true;
    private ?\Closure $autocompleterCallback = null;
    private ?\Closure $validator = null;
    private string|int|bool|float|null $default;
    private ?\Closure $normalizer = null;
    private bool $trimmable = true;
    private bool $multiline = false;

    /**
     * @param string                     $question The question to ask to the user
     * @param string|bool|int|float|null $default  The default answer to return if the user enters nothing
     */
    public function __construct(string $question, string|bool|int|float|null $default = null)
    {
        $this->question = $question;
        $this->default = $default;
    }

    /**
     * Returns the question.
	 * 返回问题
     */
    public function getQuestion(): string
    {
        return $this->question;
    }

    /**
     * Returns the default answer.
	 * 返回默认答案
     */
    public function getDefault(): string|bool|int|float|null
    {
        return $this->default;
    }

    /**
     * Returns whether the user response accepts newline characters.
	 * 返回用户响应是否接受换行字符
     */
    public function isMultiline(): bool
    {
        return $this->multiline;
    }

    /**
     * Sets whether the user response should accept newline characters.
	 * 设置用户响应是否接受换行字符
     *
     * @return $this
     */
    public function setMultiline(bool $multiline): static
    {
        $this->multiline = $multiline;

        return $this;
    }

    /**
     * Returns whether the user response must be hidden.
	 * 返回是否必须隐藏用户响应
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * Sets whether the user response must be hidden or not.
	 * 设置用户响应是否必须隐藏
     *
     * @return $this
     *
     * @throws LogicException In case the autocompleter is also used
     */
    public function setHidden(bool $hidden): static
    {
        if ($this->autocompleterCallback) {
            throw new LogicException('A hidden question cannot use the autocompleter.');
        }

        $this->hidden = $hidden;

        return $this;
    }

    /**
     * In case the response cannot be hidden, whether to fallback on non-hidden question or not.
	 * 在无法隐藏答案的情况下，是否退回到非隐藏问题。
     */
    public function isHiddenFallback(): bool
    {
        return $this->hiddenFallback;
    }

    /**
     * Sets whether to fallback on non-hidden question if the response cannot be hidden.
	 * 设置如果无法隐藏回答，是否退回到非隐藏问题。
     *
     * @return $this
     */
    public function setHiddenFallback(bool $fallback): static
    {
        $this->hiddenFallback = $fallback;

        return $this;
    }

    /**
     * Gets values for the autocompleter.
	 * 获取自动补全器的值
     */
    public function getAutocompleterValues(): ?iterable
    {
        $callback = $this->getAutocompleterCallback();

        return $callback ? $callback('') : null;
    }

    /**
     * Sets values for the autocompleter.
	 * 为自动补全设置值
     *
     * @return $this
     *
     * @throws LogicException
     */
    public function setAutocompleterValues(?iterable $values): static
    {
        if (\is_array($values)) {
            $values = $this->isAssoc($values) ? array_merge(array_keys($values), array_values($values)) : array_values($values);

            $callback = static fn () => $values;
        } elseif ($values instanceof \Traversable) {
            $callback = static function () use ($values) {
                static $valueCache;

                return $valueCache ??= iterator_to_array($values, false);
            };
        } else {
            $callback = null;
        }

        return $this->setAutocompleterCallback($callback);
    }

    /**
     * Gets the callback function used for the autocompleter.
	 * 获取用于自动补全的回调函数
     */
    public function getAutocompleterCallback(): ?callable
    {
        return $this->autocompleterCallback;
    }

    /**
     * Sets the callback function used for the autocompleter.
	 * 设置用于自动补全的回调函数。
     *
     * The callback is passed the user input as argument and should return an iterable of corresponding suggestions.
     *
     * @return $this
     */
    public function setAutocompleterCallback(?callable $callback = null): static
    {
        if (1 > \func_num_args()) {
            trigger_deprecation('symfony/console', '6.2', 'Calling "%s()" without any arguments is deprecated, pass null explicitly instead.', __METHOD__);
        }
        if ($this->hidden && null !== $callback) {
            throw new LogicException('A hidden question cannot use the autocompleter.');
        }

        $this->autocompleterCallback = null === $callback ? null : $callback(...);

        return $this;
    }

    /**
     * Sets a validator for the question.
	 * 为问题设置验证器
     *
     * @return $this
     */
    public function setValidator(?callable $validator = null): static
    {
        if (1 > \func_num_args()) {
            trigger_deprecation('symfony/console', '6.2', 'Calling "%s()" without any arguments is deprecated, pass null explicitly instead.', __METHOD__);
        }
        $this->validator = null === $validator ? null : $validator(...);

        return $this;
    }

    /**
     * Gets the validator for the question.
	 * 获取问题的验证器
     */
    public function getValidator(): ?callable
    {
        return $this->validator;
    }

    /**
     * Sets the maximum number of attempts.
	 * 设置最大尝试次数。
     *
     * Null means an unlimited number of attempts.
	 * Null表示无限制的尝试次数。
     *
     * @return $this
     *
     * @throws InvalidArgumentException in case the number of attempts is invalid
     */
    public function setMaxAttempts(?int $attempts): static
    {
        if (null !== $attempts && $attempts < 1) {
            throw new InvalidArgumentException('Maximum number of attempts must be a positive value.');
        }

        $this->attempts = $attempts;

        return $this;
    }

    /**
     * Gets the maximum number of attempts.
	 * 获取尝试的最大次数。
     *
     * Null means an unlimited number of attempts.
	 * Null表示无限制的尝试次数。
     */
    public function getMaxAttempts(): ?int
    {
        return $this->attempts;
    }

    /**
     * Sets a normalizer for the response.
	 * 为响应设置一个规范化程序。
     *
     * The normalizer can be a callable (a string), a closure or a class implementing __invoke.
     *
     * @return $this
     */
    public function setNormalizer(callable $normalizer): static
    {
        $this->normalizer = $normalizer(...);

        return $this;
    }

    /**
     * Gets the normalizer for the response.
	 * 获取响应的规范化程序。
     *
     * The normalizer can ba a callable (a string), a closure or a class implementing __invoke.
     */
    public function getNormalizer(): ?callable
    {
        return $this->normalizer;
    }

    /**
     * @return bool
     */
    protected function isAssoc(array $array)
    {
        return (bool) \count(array_filter(array_keys($array), 'is_string'));
    }

    public function isTrimmable(): bool
    {
        return $this->trimmable;
    }

    /**
     * @return $this
     */
    public function setTrimmable(bool $trimmable): static
    {
        $this->trimmable = $trimmable;

        return $this;
    }
}
