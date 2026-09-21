<?php
/**
 * Psy，反射，反射常数
 */

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2023 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\Reflection;

/**
 * Somehow the standard reflection library doesn't include constants.
 * 不知何故，标准反射库不包括常量。
 *
 * ReflectionConstant corrects that omission.
 */
class ReflectionConstant implements \Reflector
{
    public $name;
    /** @var mixed */
    private $value;

    private const MAGIC_CONSTANTS = [
        '__LINE__',
        '__FILE__',
        '__DIR__',
        '__FUNCTION__',
        '__CLASS__',
        '__TRAIT__',
        '__METHOD__',
        '__NAMESPACE__',
        '__COMPILER_HALT_OFFSET__',
    ];

    /**
     * Construct a ReflectionConstant object.
	 * 构造一个ReflectionConstant对象
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;

        if (!\defined($name) && !self::isMagicConstant($name)) {
            throw new \InvalidArgumentException('Unknown constant: '.$name);
        }

        if (!self::isMagicConstant($name)) {
            $this->value = @\constant($name);
        }
    }

    /**
     * Exports a reflection.
	 * 导出一个反射
     *
     * @param string $name
     * @param bool   $return pass true to return the export, as opposed to emitting it
     *
     * @return string|null
     */
    public static function export(string $name, bool $return = false)
    {
        $refl = new self($name);
        $value = $refl->getValue();

        $str = \sprintf('Constant [ %s %s ] { %s }', \gettype($value), $refl->getName(), $value);

        if ($return) {
            return $str;
        }

        echo $str."\n";
    }

    public static function isMagicConstant($name)
    {
        return \in_array($name, self::MAGIC_CONSTANTS);
    }

    /**
     * Get the constant's docblock.
	 * 获取常量的docblock
     *
     * @return false
     */
    public function getDocComment(): bool
    {
        return false;
    }

    /**
     * Gets the constant name.
	 * 获取常量名称
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the namespace name.
	 * 获取命名空间名称
     *
     * Returns '' when the constant is not namespaced.
     */
    public function getNamespaceName(): string
    {
        if (!$this->inNamespace()) {
            return '';
        }

        return \preg_replace('/\\\\[^\\\\]+$/', '', $this->name);
    }

    /**
     * Gets the value of the constant.
	 * 获取常量的值
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Checks if this constant is defined in a namespace.
	 * 检查该常量是否在命名空间中定义
     */
    public function inNamespace(): bool
    {
        return \strpos($this->name, '\\') !== false;
    }

    /**
     * To string.
	 * 转换为字符串
     */
    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * Gets the constant's file name.
	 * 获取常量的文件名。
     *
     * Currently returns null, because if it returns a file name the signature
     * formatter will barf.
     */
    public function getFileName()
    {
        return;
        // return $this->class->getFileName();
    }

    /**
     * Get the code start line.
	 * 获取代码起始行
     *
     * @throws \RuntimeException
     */
    public function getStartLine()
    {
        throw new \RuntimeException('Not yet implemented because it\'s unclear what I should do here :)');
    }

    /**
     * Get the code end line.
	 * 获取代码结束行
     *
     * @throws \RuntimeException
     */
    public function getEndLine()
    {
        return $this->getStartLine();
    }
}
