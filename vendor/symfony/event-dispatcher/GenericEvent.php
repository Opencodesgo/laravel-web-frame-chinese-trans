<?php
/**
 * Symfony，Component，EventDispatcher，普通事件
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\EventDispatcher;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event encapsulation class.
 * 事件封装类。
 *
 * Encapsulates events thus decoupling the observer from the subject they encapsulate.
 * 封装事件，从而将观察者与其封装的主题解耦。
 *
 * @author Drak <drak@zikula.org>
 *
 * @implements \ArrayAccess<string, mixed>
 * @implements \IteratorAggregate<string, mixed>
 */
class GenericEvent extends Event implements \ArrayAccess, \IteratorAggregate
{
    protected $subject;
    protected $arguments;

    /**
     * Encapsulate an event with $subject and $arguments.
	 * 用$subject和$arguments封装一个事件
     *
     * @param mixed $subject   The subject of the event, usually an object or a callable
     * @param array $arguments Arguments to store in the event
     */
    public function __construct(mixed $subject = null, array $arguments = [])
    {
        $this->subject = $subject;
        $this->arguments = $arguments;
    }

    /**
     * Getter for subject property.
	 * 主体属性的Getter
     */
    public function getSubject(): mixed
    {
        return $this->subject;
    }

    /**
     * Get argument by key.
	 * 按键获取参数
     *
     * @throws \InvalidArgumentException if key is not found
     */
    public function getArgument(string $key): mixed
    {
        if ($this->hasArgument($key)) {
            return $this->arguments[$key];
        }

        throw new \InvalidArgumentException(\sprintf('Argument "%s" not found.', $key));
    }

    /**
     * Add argument to event.
	 * 向事件添加参数
     *
     * @return $this
     */
    public function setArgument(string $key, mixed $value): static
    {
        $this->arguments[$key] = $value;

        return $this;
    }

    /**
     * Getter for all arguments.
	 * 所有参数的Getter
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Set args property.
	 * 设置args属性
     *
     * @return $this
     */
    public function setArguments(array $args = []): static
    {
        $this->arguments = $args;

        return $this;
    }

    /**
     * Has argument.
	 * 有参数
     */
    public function hasArgument(string $key): bool
    {
        return \array_key_exists($key, $this->arguments);
    }

    /**
     * ArrayAccess for argument getter.
	 * 参数getter的ArrayAccess
     *
     * @param string $key Array key
     *
     * @throws \InvalidArgumentException if key does not exist in $this->args
     */
    public function offsetGet(mixed $key): mixed
    {
        return $this->getArgument($key);
    }

    /**
     * ArrayAccess for argument setter.
	 * 参数设置器的ArrayAccess
     *
     * @param string $key Array key to set
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->setArgument($key, $value);
    }

    /**
     * ArrayAccess for unset argument.
	 * 用于未设置参数的ArrayAccess
     *
     * @param string $key Array key
     */
    public function offsetUnset(mixed $key): void
    {
        if ($this->hasArgument($key)) {
            unset($this->arguments[$key]);
        }
    }

    /**
     * ArrayAccess has argument.
	 * ArrayAccess有参数
     *
     * @param string $key Array key
     */
    public function offsetExists(mixed $key): bool
    {
        return $this->hasArgument($key);
    }

    /**
     * IteratorAggregate for iterating over the object like an array.
	 * IteratorAggregate用于像数组一样遍历对象
     *
     * @return \ArrayIterator<string, mixed>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->arguments);
    }
}
