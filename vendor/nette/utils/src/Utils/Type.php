<?php
/**
 * Nette，Utils，类型
 */

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Utils;

use Nette;
use function array_map, array_search, array_splice, count, explode, implode, is_a, is_string, strcasecmp, strtolower, substr, trim;
use const PHP_VERSION_ID;


/**
 * PHP type reflection.
 * PHP 类型反射。
 */
final class Type
{
	/** @var array<int, string|self> */
	private array $types;
	private bool $simple;
	private string $kind; // | &


	/**
	 * Creates a Type object based on reflection. Resolves self, static and parent to the actual class name.
	 * If the subject has no type, it returns null.
	 * 基于反射创建Type对象。将self、static和parent解析为实际的类名。
	 */
	public static function fromReflection(
		\ReflectionFunctionAbstract|\ReflectionParameter|\ReflectionProperty $reflection,
	): ?self
	{
		$type = $reflection instanceof \ReflectionFunctionAbstract
			? $reflection->getReturnType() ?? (PHP_VERSION_ID >= 80100 && $reflection instanceof \ReflectionMethod ? $reflection->getTentativeReturnType() : null)
			: $reflection->getType();

		return $type ? self::fromReflectionType($type, $reflection, asObject: true) : null;
	}


	private static function fromReflectionType(\ReflectionType $type, $of, bool $asObject): self|string
	{
		if ($type instanceof \ReflectionNamedType) {
			$name = self::resolve($type->getName(), $of);
			return $asObject
				? new self($type->allowsNull() && $name !== 'mixed' ? [$name, 'null'] : [$name])
				: $name;

		} elseif ($type instanceof \ReflectionUnionType || $type instanceof \ReflectionIntersectionType) {
			return new self(
				array_map(fn($t) => self::fromReflectionType($t, $of, asObject: false), $type->getTypes()),
				$type instanceof \ReflectionUnionType ? '|' : '&',
			);

		} else {
			throw new Nette\InvalidStateException('Unexpected type of ' . Reflection::toString($of));
		}
	}


	/**
	 * Creates the Type object according to the text notation.
	 * 根据文本符号创建Type对象
	 */
	public static function fromString(string $type): self
	{
		if (!Validators::isTypeDeclaration($type)) {
			throw new Nette\InvalidArgumentException("Invalid type '$type'.");
		}

		if ($type[0] === '?') {
			return new self([substr($type, 1), 'null']);
		}

		$unions = [];
		foreach (explode('|', $type) as $part) {
			$part = explode('&', trim($part, '()'));
			$unions[] = count($part) === 1 ? $part[0] : new self($part, '&');
		}

		return count($unions) === 1 && $unions[0] instanceof self
			? $unions[0]
			: new self($unions);
	}


	/**
	 * Resolves 'self', 'static' and 'parent' to the actual class name.
	 */
	public static function resolve(
		string $type,
		\ReflectionFunctionAbstract|\ReflectionParameter|\ReflectionProperty $of,
	): string
	{
		$lower = strtolower($type);
		if ($of instanceof \ReflectionFunction) {
			return $type;
		} elseif ($lower === 'self') {
			return $of->getDeclaringClass()->name;
		} elseif ($lower === 'static') {
			return ($of instanceof ReflectionMethod ? $of->getOriginalClass() : $of->getDeclaringClass())->name;
		} elseif ($lower === 'parent' && $of->getDeclaringClass()->getParentClass()) {
			return $of->getDeclaringClass()->getParentClass()->name;
		} else {
			return $type;
		}
	}


	private function __construct(array $types, string $kind = '|')
	{
		$o = array_search('null', $types, strict: true);
		if ($o !== false) { // null as last
			array_splice($types, $o, 1);
			$types[] = 'null';
		}

		$this->types = $types;
		$this->simple = is_string($types[0]) && ($types[1] ?? 'null') === 'null';
		$this->kind = count($types) > 1 ? $kind : '';
	}


	public function __toString(): string
	{
		$multi = count($this->types) > 1;
		if ($this->simple) {
			return ($multi ? '?' : '') . $this->types[0];
		}

		$res = [];
		foreach ($this->types as $type) {
			$res[] = $type instanceof self && $multi ? "($type)" : $type;
		}
		return implode($this->kind, $res);
	}


	/**
	 * Returns the array of subtypes that make up the compound type as strings.
	 * 将组成复合类型的子类型数组返回为字符串
	 * @return array<int, string|string[]>
	 */
	public function getNames(): array
	{
		return array_map(fn($t) => $t instanceof self ? $t->getNames() : $t, $this->types);
	}


	/**
	 * Returns the array of subtypes that make up the compound type as Type objects:
	 * 返回组成复合类型的子类型数组作为type对象：
	 * @return self[]
	 */
	public function getTypes(): array
	{
		return array_map(fn($t) => $t instanceof self ? $t : new self([$t]), $this->types);
	}


	/**
	 * Returns the type name for simple types, otherwise null.
	 * 返回简单类型的类型名，否则为空。
	 */
	public function getSingleName(): ?string
	{
		return $this->simple
			? $this->types[0]
			: null;
	}


	/**
	 * Returns true whether it is a union type.
	 * 无论是否是联合类型，都返回true。
	 */
	public function isUnion(): bool
	{
		return $this->kind === '|';
	}


	/**
	 * Returns true whether it is an intersection type.
	 * 无论是否为交集类型，都返回true。
	 */
	public function isIntersection(): bool
	{
		return $this->kind === '&';
	}


	/**
	 * Returns true whether it is a simple type. Single nullable types are also considered to be simple types.
	 */
	public function isSimple(): bool
	{
		return $this->simple;
	}


	/** @deprecated use isSimple() */
	public function isSingle(): bool
	{
		return $this->simple;
	}


	/**
	 * Returns true whether the type is both a simple and a PHP built-in type.
	 * 无论类型是简单类型还是PHP内置类型，都返回true。
	 */
	public function isBuiltin(): bool
	{
		return $this->simple && Validators::isBuiltinType($this->types[0]);
	}


	/**
	 * Returns true whether the type is both a simple and a class name.
	 * 无论类型是简单类型还是类名，都返回true。
	 */
	public function isClass(): bool
	{
		return $this->simple && !Validators::isBuiltinType($this->types[0]);
	}


	/**
	 * Determines if type is special class name self/parent/static.
	 * 确定类型是否为特殊类名self/parent/static。
	 */
	public function isClassKeyword(): bool
	{
		return $this->simple && Validators::isClassKeyword($this->types[0]);
	}


	/**
	 * Verifies type compatibility. For example, it checks if a value of a certain type could be passed as a parameter.
	 */
	public function allows(string $subtype): bool
	{
		if ($this->types === ['mixed']) {
			return true;
		}

		$subtype = self::fromString($subtype);
		return $subtype->isUnion()
			? Arrays::every($subtype->types, fn($t) => $this->allows2($t instanceof self ? $t->types : [$t]))
			: $this->allows2($subtype->types);
	}


	private function allows2(array $subtypes): bool
	{
		return $this->isUnion()
			? Arrays::some($this->types, fn($t) => $this->allows3($t instanceof self ? $t->types : [$t], $subtypes))
			: $this->allows3($this->types, $subtypes);
	}


	private function allows3(array $types, array $subtypes): bool
	{
		return Arrays::every(
			$types,
			fn($type) => Arrays::some(
				$subtypes,
				fn($subtype) => Validators::isBuiltinType($type)
					? strcasecmp($type, $subtype) === 0
					: is_a($subtype, $type, allow_string: true),
			),
		);
	}
}
