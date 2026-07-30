<?php
/**
 * Dotenv，存储，文件存储
 */

namespace Dotenv\Store;

use Dotenv\Exception\InvalidPathException;
use Dotenv\Store\File\Reader;

class FileStore implements StoreInterface
{
    /**
     * The file paths.
	 * 文件路径
     *
     * @var string[]
     */
    protected $filePaths;

    /**
     * Should file loading short circuit?
	 * 文件加载应该短路吗？
     *
     * @var bool
     */
    protected $shortCircuit;

    /**
     * Create a new file store instance.
	 * 创建一个新的文件存储实例
     *
     * @param string[] $filePaths
     * @param bool     $shortCircuit
     *
     * @return void
     */
    public function __construct(array $filePaths, $shortCircuit)
    {
        $this->filePaths = $filePaths;
        $this->shortCircuit = $shortCircuit;
    }

    /**
     * Read the content of the environment file(s).
     *
     * @throws \Dotenv\Exception\InvalidPathException
     *
     * @return string
     */
    public function read()
    {
        if ($this->filePaths === []) {
            throw new InvalidPathException('At least one environment file path must be provided.');
        }

        $contents = Reader::read($this->filePaths, $this->shortCircuit);

        if (count($contents) > 0) {
            return implode("\n", $contents);
        }

        throw new InvalidPathException(
            sprintf('Unable to read any of the environment file(s) at [%s].', implode(', ', $this->filePaths))
        );
    }
}
