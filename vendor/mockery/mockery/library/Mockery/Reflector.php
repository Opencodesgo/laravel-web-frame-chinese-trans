<?php
/**
 * Mockery，反射
 */

/**
 * Mockery (https://docs.mockery.io/)
 *
 * @copyright https://github.com/mockery/mockery/blob/HEAD/COPYRIGHT.md
 * @license https://github.com/mockery/mockery/blob/HEAD/LICENSE BSD 3-Clause License
 * @link https://github.com/mockery/mockery for the canonical source repository
 */

namespace Mockery;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;

use function array_diff;
use function array_intersect;
use function array_map;
use function array_merge;
use function get_debug_type;
use function implode;
use function in_array;
use function method_exists;
use function sprintf;
use function strpos;

use const PHP_VERSION_ID;

/**
 * @internal
 */
class Reflector
{
    /**
     * List of built-in types.
	 * 内置类型列表
     *
     * @var list<string>
     */
    public const BUILTIN_TYPES = ['array', 'bool', 'int', 'float', 'null', 'object', 'string'];

    /**
     * List of reserved words.
	 * 保留词的列表
     *
     * @var list<string>
     */
    public const RESERVED_WORDS = ['bool', 'true', 'false', 'float', 'int', 'iterable', 'mixed', 'never', 'null', 'object', 'string', 'void'];

    /**
     * Iterable.
	 * 可迭代的
     *
     * @var list<string>
     */
    private const ITERABLE = ['iterable'];

    /**
     * Traversable array.
	 * 可移动数组
     *
     * @var list<string>
     */
    private const TRAVERSABLE_ARRAY = ['\Traversable', 'array'];

    /**
     * Compute the string representation for the return type.
	 * 计算返回类型的字符串表示
     *
     * @param bool $withoutNullable
     *
     * @return null|string
     */
    public static function getReturnType(ReflectionMethod $method, $withoutNullable = false)
    {
        $type = $method->getReturnType();

        if (! $type instanceof ReflectionType && method_exists($method, 'getTentativeReturnType')) {
            $type = $method->getTentativeReturnType();
        }

        if (! $type instanceof ReflectionType) {
            return null;
        }

        $typeHint = self::getTypeFromReflectionType($type, $method->getDeclaringClass());

        return (! $withoutNullable && $type->allowsNull()) ? self::formatNullableType($typeHint) : $typeHint;
    }

    /**
     * Compute the string representation for the simplest return type.
	 * 计算最简单返回类型的字符串表示
     *
     * @return null|string
     */
    public static function getSimplestReturnType(ReflectionMethod $method)
    {
        $type = $method->getReturnType();

        if (! $type instanceof ReflectionType && method_exists($method, 'getTentativeReturnType')) {
            $type = $method->getTentativeReturnType();
        }

        if (! $type instanceof ReflectionType || $type->allowsNull()) {
            return null;
        }

        $typeInformation = self::getTypeInformation($type, $method->getDeclaringClass());

        // return the first primitive type hint
		// 返回第一个基本类型提示
        foreach ($typeInformation as $info) {
            if ($info['isPrimitive']) {
                return $info['typeHint'];
            }
        }

        // if no primitive type, return the first type
		// 如果没有基本类型，则返回第一个类型。
        foreach ($typeInformation as $info) {
            return $info['typeHint'];
        }

        return null;
    }

    /**
     * Compute the string representation for the paramater type.
	 * 计算履佩式类型的字符串表示
     *
     * @param bool $withoutNullable
     *
     * @return null|string
     */
    public static function getTypeHint(ReflectionParameter $param, $withoutNullable = false)
    {
        if (! $param->hasType()) {
            return null;
        }

        $type = $param->getType();
        $declaringClass = $param->getDeclaringClass();
        $typeHint = self::getTypeFromReflectionType($type, $declaringClass);

        return (! $withoutNullable && $type->allowsNull()) ? self::formatNullableType($typeHint) : $typeHint;
    }

    /**
     * Determine if the parameter is typed as an array.
	 * 确定参数是否被输入为数组
     *
     * @return bool
     */
    public static function isArray(ReflectionParameter $param)
    {
        $type = $param->getType();

        return $type instanceof ReflectionNamedType && $type->getName();
    }

    /**
     * Determine if the given type is a reserved word.
	 * 确定给定类型是否是保留词
     */
    public static function isReservedWord(string $type): bool
    {
        return in_array(strtolower($type), self::RESERVED_WORDS, true);
    }

    /**
     * Format the given type as a nullable type.
	 * 将给定类型格式格式化为可空类型
     */
    private static function formatNullableType(string $typeHint): string
    {
        if ($typeHint === 'mixed') {
            return $typeHint;
        }

        if (strpos($typeHint, 'null') !== false) {
            return $typeHint;
        }

        if (PHP_VERSION_ID < 80000) {
            return sprintf('?%s', $typeHint);
        }

        return sprintf('%s|null', $typeHint);
    }

    private static function getTypeFromReflectionType(ReflectionType $type, ReflectionClass $declaringClass): string
    {
        if ($type instanceof ReflectionNamedType) {
            $typeHint = $type->getName();

            if ($type->isBuiltin()) {
                return $typeHint;
            }

            if ($typeHint === 'static') {
                return $typeHint;
            }

            // 'self' needs to be resolved to the name of the declaring class
			// ‘self’需要被解析为声明类的名称
            if ($typeHint === 'self') {
                $typeHint = $declaringClass->getName();
            }

            // 'parent' needs to be resolved to the name of the parent class
			// ‘parent’需要解析为父类的名称
            if ($typeHint === 'parent') {
                $typeHint = $declaringClass->getParentClass()->getName();
            }

            // class names need prefixing with a slash
			// 类名需要用斜杠作为前缀
            return sprintf('\\%s', $typeHint);
        }

        if ($type instanceof ReflectionIntersectionType) {
            $types = array_map(
                static function (ReflectionType $type) use ($declaringClass): string {
                    return self::getTypeFromReflectionType($type, $declaringClass);
                },
                $type->getTypes()
            );

            return implode('&', $types);
        }

        if ($type instanceof ReflectionUnionType) {
            $types = array_map(
                static function (ReflectionType $type) use ($declaringClass): string {
                    return self::getTypeFromReflectionType($type, $declaringClass);
                },
                $type->getTypes()
            );

            $intersect = array_intersect(self::TRAVERSABLE_ARRAY, $types);
            if ($intersect === self::TRAVERSABLE_ARRAY) {
                $types = array_merge(self::ITERABLE, array_diff($types, self::TRAVERSABLE_ARRAY));
            }

            return implode(
                '|',
                array_map(
                    static function (string $type): string {
                        return strpos($type, '&') === false ? $type : sprintf('(%s)', $type);
                    },
                    $types
                )
            );
        }

        throw new InvalidArgumentException('Unknown ReflectionType: ' . get_debug_type($type));
    }

    /**
     * Get the string representation of the given type.
	 * 获取给定类型的字符串表示
     *
     * @return list<array{typeHint:string,isPrimitive:bool}>
     */
    private static function getTypeInformation(ReflectionType $type, ReflectionClass $declaringClass): array
    {
        // PHP 8 union types and PHP 8.1 intersection types can be recursively processed
		// PHP 8的联合类型和PHP 8.1的交集类型可以递归处理
        if ($type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType) {
            $types = [];

            foreach ($type->getTypes() as $innterType) {
                foreach (self::getTypeInformation($innterType, $declaringClass) as $info) {
                    if ($info['typeHint'] === 'null' && $info['isPrimitive']) {
                        continue;
                    }

                    $types[] = $info;
                }
            }

            return $types;
        }

        // $type must be an instance of \ReflectionNamedType
        $typeHint = $type->getName();

        // builtins can be returned as is
        if ($type->isBuiltin()) {
            return [
                [
                    'typeHint' => $typeHint,
                    'isPrimitive' => in_array($typeHint, self::BUILTIN_TYPES, true),
                ],
            ];
        }

        // 'static' can be returned as is
        if ($typeHint === 'static') {
            return [
                [
                    'typeHint' => $typeHint,
                    'isPrimitive' => false,
                ],
            ];
        }

        // 'self' needs to be resolved to the name of the declaring class
		// ‘self’需要被解析为声明类的名称
        if ($typeHint === 'self') {
            $typeHint = $declaringClass->getName();
        }

        // 'parent' needs to be resolved to the name of the parent class
		// ‘parent’需要解析为父类的名称
        if ($typeHint === 'parent') {
            $typeHint = $declaringClass->getParentClass()->getName();
        }

        // class names need prefixing with a slash
		// 类名需要用斜杠前缀
        return [
            [
                'typeHint' => sprintf('\\%s', $typeHint),
                'isPrimitive' => false,
            ],
        ];
    }
}
