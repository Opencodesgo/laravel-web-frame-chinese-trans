<?php
/**
 * Symfony，Contracts，String，Lazy 字符串
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\String;

/**
 * A string whose value is computed lazily by a callback.
 * 一个字符串，其值由回调惰性计算。
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class LazyString implements \Stringable, \JsonSerializable
{
    private \Closure|string $value;

    /**
     * @param callable|array $callback A callable or a [Closure, method] lazy-callable
     */
    public static function fromCallable(callable|array $callback, mixed ...$arguments): static
    {
        if (\is_array($callback) && !\is_callable($callback) && !(($callback[0] ?? null) instanceof \Closure || 2 < \count($callback))) {
            throw new \TypeError(\sprintf('Argument 1 passed to "%s()" must be a callable or a [Closure, method] lazy-callable, "%s" given.', __METHOD__, '['.implode(', ', array_map('get_debug_type', $callback)).']'));
        }

        $lazyString = new static();
        $lazyString->value = static function () use (&$callback, &$arguments): string {
            static $value;

            if (null !== $arguments) {
                if (!\is_callable($callback)) {
                    $callback[0] = $callback[0]();
                    $callback[1] ??= '__invoke';
                }
                $value = $callback(...$arguments);
                $callback = !\is_scalar($value) && !$value instanceof \Stringable ? self::getPrettyName($callback) : 'callable';
                $arguments = null;
            }

            return $value ?? '';
        };

        return $lazyString;
    }

    public static function fromStringable(string|int|float|bool|\Stringable $value): static
    {
        if (\is_object($value)) {
            return static::fromCallable($value->__toString(...));
        }

        $lazyString = new static();
        $lazyString->value = (string) $value;

        return $lazyString;
    }

    /**
     * Tells whether the provided value can be cast to string.
	 * 告诉所提供的值是否可以强制转换为字符串
     */
    final public static function isStringable(mixed $value): bool
    {
        return \is_string($value) || $value instanceof \Stringable || \is_scalar($value);
    }

    /**
     * Casts scalars and stringable objects to strings.
	 * 将标量和可字符串对象强制转换为字符串
     *
     * @throws \TypeError When the provided value is not stringable
     */
    final public static function resolve(\Stringable|string|int|float|bool $value): string
    {
        return $value;
    }

    public function __toString(): string
    {
        if (\is_string($this->value)) {
            return $this->value;
        }

        try {
            return $this->value = ($this->value)();
        } catch (\Throwable $e) {
            if (\TypeError::class === $e::class && __FILE__ === $e->getFile()) {
                $type = explode(', ', $e->getMessage());
                $type = substr(array_pop($type), 0, -\strlen(' returned'));
                $r = new \ReflectionFunction($this->value);
                $callback = $r->getStaticVariables()['callback'];

                $e = new \TypeError(\sprintf('Return value of %s() passed to %s::fromCallable() must be of the type string, %s returned.', $callback, static::class, $type));
            }

            throw $e;
        }
    }

    public function __sleep(): array
    {
        $this->__toString();

        return ['value'];
    }

    public function jsonSerialize(): string
    {
        return $this->__toString();
    }

    private function __construct()
    {
    }

    private static function getPrettyName(callable $callback): string
    {
        if (\is_string($callback)) {
            return $callback;
        }

        if (\is_array($callback)) {
            $class = \is_object($callback[0]) ? get_debug_type($callback[0]) : $callback[0];
            $method = $callback[1];
        } elseif ($callback instanceof \Closure) {
            $r = new \ReflectionFunction($callback);

            if (str_contains($r->name, '{closure') || !$class = \PHP_VERSION_ID >= 80111 ? $r->getClosureCalledClass() : $r->getClosureScopeClass()) {
                return $r->name;
            }

            $class = $class->name;
            $method = $r->name;
        } else {
            $class = get_debug_type($callback);
            $method = '__invoke';
        }

        return $class.'::'.$method;
    }
}
