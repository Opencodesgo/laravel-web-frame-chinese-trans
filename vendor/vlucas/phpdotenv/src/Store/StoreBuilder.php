<?php
/**
 * Dotenv，存储，存储构建器
 */

namespace Dotenv\Store;

use Dotenv\Store\File\Paths;

class StoreBuilder
{
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
     *
     * @var string[]|null
     */
    private $names;

    /**
     * Should file loading short circuit?
	 * 文件加载应该短路吗？
     *
     * @var bool
     */
    protected $shortCircuit;

    /**
     * Create a new store builder instance.
	 * 创建一个新的存储构建器实例
     *
     * @param string[]      $paths
     * @param string[]|null $names
     * @param bool          $shortCircuit
     *
     * @return void
     */
    private function __construct(array $paths = [], array $names = null, $shortCircuit = false)
    {
        $this->paths = $paths;
        $this->names = $names;
        $this->shortCircuit = $shortCircuit;
    }

    /**
     * Create a new store builder instance.
	 * 创建一个新的存储构建器实例
     *
     * @return \Dotenv\Store\StoreBuilder
     */
    public static function create()
    {
        return new self();
    }

    /**
     * Creates a store builder with the given paths.
	 * 使用给定路径创建存储构建器
     *
     * @param string|string[] $paths
     *
     * @return \Dotenv\Store\StoreBuilder
     */
    public function withPaths($paths)
    {
        return new self((array) $paths, $this->names, $this->shortCircuit);
    }

    /**
     * Creates a store builder with the given names.
	 * 用给定的名称创建一个存储生成器
     *
     * @param string|string[]|null $names
     *
     * @return \Dotenv\Store\StoreBuilder
     */
    public function withNames($names = null)
    {
        return new self($this->paths, $names === null ? null : (array) $names, $this->shortCircuit);
    }

    /**
     * Creates a store builder with short circuit mode enabled.
     *
     * @return \Dotenv\Store\StoreBuilder
     */
    public function shortCircuit()
    {
        return new self($this->paths, $this->names, true);
    }

    /**
     * Creates a new store instance.
     *
     * @return \Dotenv\Store\StoreInterface
     */
    public function make()
    {
        return new FileStore(
            Paths::filePaths($this->paths, $this->names === null ? ['.env'] : $this->names),
            $this->shortCircuit
        );
    }
}
