<?php
/**
 * Dotenv，资源库，适配器，多作者
 */

declare(strict_types=1);

namespace Dotenv\Repository\Adapter;

final class MultiWriter implements WriterInterface
{
    /**
     * The set of writers to use.
	 * 要使用的书写器的集合
     *
     * @var \Dotenv\Repository\Adapter\WriterInterface[]
     */
    private $writers;

    /**
     * Create a new multi-writer instance.
	 * 创建一个新的多写入器实例
     *
     * @param \Dotenv\Repository\Adapter\WriterInterface[] $writers
     *
     * @return void
     */
    public function __construct(array $writers)
    {
        $this->writers = $writers;
    }

    /**
     * Write to an environment variable, if possible.
	 * 如果可能的话，写入环境变量。
     *
     * @param non-empty-string $name
     * @param string           $value
     *
     * @return bool
     */
    public function write(string $name, string $value)
    {
        foreach ($this->writers as $writers) {
            if (!$writers->write($name, $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Delete an environment variable, if possible.
     *
     * @param non-empty-string $name
     *
     * @return bool
     */
    public function delete(string $name)
    {
        foreach ($this->writers as $writers) {
            if (!$writers->delete($name)) {
                return false;
            }
        }

        return true;
    }
}
