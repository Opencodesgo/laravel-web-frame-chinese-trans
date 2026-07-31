<?php
/**
 * Dotenv，存储，存储建造者
 */

declare(strict_types=1);

namespace Dotenv\Store;

use Dotenv\Store\File\Paths;

final class StoreBuilder
{
    /**
     * The of default name.
	 * 默认名称
     */
    private const DEFAULT_NAME = '.env';

    /**
     * The paths to search within.
	 * 寻找内在的路径
     *
     * @var string[]
     */
    private $paths;

    /**
     * The file names to search for.
	 * 要搜索的文件名
     *
     * @var string[]
     */
    private $names;

    /**
     * Should file loading short circuit?
	 * 文件加载应该短路吗
     *
     * @var bool
     */
    private $shortCircuit;

    /**
     * The file encoding.
	 * 文件编码
     *
     * @var string|null
     */
    private $fileEncoding;

    /**
     * Create a new store builder instance.
	 * 创建一个新的存储构建器实例
     *
     * @param string[]    $paths
     * @param string[]    $names
     * @param bool        $shortCircuit
     * @param string|null $fileEncoding
     *
     * @return void
     */
    private function __construct(array $paths = [], array $names = [], bool $shortCircuit = false, ?string $fileEncoding = null)
    {
        $this->paths = $paths;
        $this->names = $names;
        $this->shortCircuit = $shortCircuit;
        $this->fileEncoding = $fileEncoding;
    }

    /**
     * Create a new store builder instance with no names.
	 * 创建一个没有名称的新存储构建器实例
     *
     * @return \Dotenv\Store\StoreBuilder
     */
    public static function createWithNoNames()
    {
        return new self();
    }

    /**
     * Create a new store builder instance with the default name.
	 * 使用默认名称创建新的存储构建器实例。
     *
     * @return \Dotenv\Store\StoreBuilder
     */
    public static function createWithDefaultName()
    {
        return new self([], [self::DEFAULT_NAME]);
    }

    /**
     * Creates a store builder with the given path added.
	 * 创建添加了给定路径的存储构建器
     *
     * @param string $path
     *
     * @return \Dotenv\Store\StoreBuilder
     */
    public function addPath(string $path)
    {
        return new self(\array_merge($this->paths, [$path]), $this->names, $this->shortCircuit, $this->fileEncoding);
    }

    /**
     * Creates a store builder with the given name added.
	 * 创建添加了给定名称的存储构建器
     *
     * @param string $name
     *
     * @return \Dotenv\Store\StoreBuilder
     */
    public function addName(string $name)
    {
        return new self($this->paths, \array_merge($this->names, [$name]), $this->shortCircuit, $this->fileEncoding);
    }

    /**
     * Creates a store builder with short circuit mode enabled.
	 * 创建带有短路模式的存储器
     *
     * @return \Dotenv\Store\StoreBuilder
     */
    public function shortCircuit()
    {
        return new self($this->paths, $this->names, true, $this->fileEncoding);
    }

    /**
     * Creates a store builder with the specified file encoding.
	 * 使用指定的文件编码创建存储构建器
     *
     * @param string|null $fileEncoding
     *
     * @return \Dotenv\Store\StoreBuilder
     */
    public function fileEncoding(?string $fileEncoding = null)
    {
        return new self($this->paths, $this->names, $this->shortCircuit, $fileEncoding);
    }

    /**
     * Creates a new store instance.
	 * 创建一个新的存储实例
     *
     * @return \Dotenv\Store\StoreInterface
     */
    public function make()
    {
        return new FileStore(
            Paths::filePaths($this->paths, $this->names),
            $this->shortCircuit,
            $this->fileEncoding
        );
    }
}
