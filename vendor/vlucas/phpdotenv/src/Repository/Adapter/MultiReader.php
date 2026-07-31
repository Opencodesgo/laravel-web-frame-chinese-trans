<?php
/**
 * Dotenv，Repository，适配器，多阅读器
 */

declare(strict_types=1);

namespace Dotenv\Repository\Adapter;

use PhpOption\None;

final class MultiReader implements ReaderInterface
{
    /**
     * The set of readers to use.
	 * 将使用的读者集
     *
     * @var \Dotenv\Repository\Adapter\ReaderInterface[]
     */
    private $readers;

    /**
     * Create a new multi-reader instance.
	 * 创建一个新的多读者实例
     *
     * @param \Dotenv\Repository\Adapter\ReaderInterface[] $readers
     *
     * @return void
     */
    public function __construct(array $readers)
    {
        $this->readers = $readers;
    }

    /**
     * Read an environment variable, if it exists.
	 * 读取环境变量,如果存在的话。
     *
     * @param non-empty-string $name
     *
     * @return \PhpOption\Option<string>
     */
    public function read(string $name)
    {
        foreach ($this->readers as $reader) {
            $result = $reader->read($name);
            if ($result->isDefined()) {
                return $result;
            }
        }

        return None::create();
    }
}
