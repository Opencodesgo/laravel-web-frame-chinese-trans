<?php declare(strict_types=1);

/**
 * PhpParser，php版本
 */

namespace PhpParser;

/**
 * A PHP version, representing only the major and minor version components.
 * 一个PHP版本，只表示主版本和次版本组件。
 */
class PhpVersion {
    /** @var int Version ID in PHP_VERSION_ID format */
    public int $id;

    /** @var int[] Minimum versions for builtin types */
    private const BUILTIN_TYPE_VERSIONS = [
        'array'    => 50100,
        'callable' => 50400,
        'bool'     => 70000,
        'int'      => 70000,
        'float'    => 70000,
        'string'   => 70000,
        'iterable' => 70100,
        'void'     => 70100,
        'object'   => 70200,
        'null'     => 80000,
        'false'    => 80000,
        'mixed'    => 80000,
        'never'    => 80100,
        'true'     => 80200,
    ];

    private function __construct(int $id) {
        $this->id = $id;
    }

    /**
     * Create a PhpVersion object from major and minor version components.
	 * 从主版本和次版本组件创建一个PhpVersion对象
     */
    public static function fromComponents(int $major, int $minor): self {
        return new self($major * 10000 + $minor * 100);
    }

    /**
     * Get the newest PHP version supported by this library. Support for this version may be partial,
     * if it is still under development.
	 * 获取此库支持的最新PHP版本。对这个版本的支持可能是部分的，
     */
    public static function getNewestSupported(): self {
        return self::fromComponents(8, 4);
    }

    /**
     * Get the host PHP version, that is the PHP version we're currently running on.
	 * 获取主机PHP版本，这是我们当前运行的PHP版本。
     */
    public static function getHostVersion(): self {
        return self::fromComponents(\PHP_MAJOR_VERSION, \PHP_MINOR_VERSION);
    }

    /**
     * Parse the version from a string like "8.1".
     */
    public static function fromString(string $version): self {
        if (!preg_match('/^(\d+)\.(\d+)/', $version, $matches)) {
            throw new \LogicException("Invalid PHP version \"$version\"");
        }
        return self::fromComponents((int) $matches[1], (int) $matches[2]);
    }

    /**
     * Check whether two versions are the same.
     */
    public function equals(PhpVersion $other): bool {
        return $this->id === $other->id;
    }

    /**
     * Check whether this version is greater than or equal to the argument.
	 * 检查此版本是否大于或等于参数
     */
    public function newerOrEqual(PhpVersion $other): bool {
        return $this->id >= $other->id;
    }

    /**
     * Check whether this version is older than the argument.
	 * 检查此版本是否比参数早
     */
    public function older(PhpVersion $other): bool {
        return $this->id < $other->id;
    }

    /**
     * Check whether this is the host PHP version.
	 * 检查这是否是主机的PHP版本
     */
    public function isHostVersion(): bool {
        return $this->equals(self::getHostVersion());
    }

    /**
     * Check whether this PHP version supports the given builtin type. Type name must be lowercase.
	 * 检查此PHP版本是否支持给定的内置类型。类型名称必须小写。
     */
    public function supportsBuiltinType(string $type): bool {
        $minVersion = self::BUILTIN_TYPE_VERSIONS[$type] ?? null;
        return $minVersion !== null && $this->id >= $minVersion;
    }

    /**
     * Whether this version supports [] array literals.
     */
    public function supportsShortArraySyntax(): bool {
        return $this->id >= 50400;
    }

    /**
     * Whether this version supports [] for destructuring.
     */
    public function supportsShortArrayDestructuring(): bool {
        return $this->id >= 70100;
    }

    /**
     * Whether this version supports flexible heredoc/nowdoc.
	 * 此版本是否支持灵活的heredoc/nowdoc
     */
    public function supportsFlexibleHeredoc(): bool {
        return $this->id >= 70300;
    }

    /**
     * Whether this version supports trailing commas in parameter lists.
	 * 此版本是否支持参数列表中的尾随逗号
     */
    public function supportsTrailingCommaInParamList(): bool {
        return $this->id >= 80000;
    }

    /**
     * Whether this version allows "$var =& new Obj".
     */
    public function allowsAssignNewByReference(): bool {
        return $this->id < 70000;
    }

    /**
     * Whether this version allows invalid octals like "08".
     */
    public function allowsInvalidOctals(): bool {
        return $this->id < 70000;
    }

    /**
     * Whether this version allows DEL (\x7f) to occur in identifiers.
     */
    public function allowsDelInIdentifiers(): bool {
        return $this->id < 70100;
    }

    /**
     * Whether this version supports yield in expression context without parentheses.
	 * 这个版本是否支持表达式上下文中不带括号的yield
     */
    public function supportsYieldWithoutParentheses(): bool {
        return $this->id >= 70000;
    }

    /**
     * Whether this version supports unicode escape sequences in strings.
	 * 此版本是否支持字符串中的unicode转义序列
     */
    public function supportsUnicodeEscapes(): bool {
        return $this->id >= 70000;
    }
}
