<?php
/**
 * Dotenv，资源库，适配器，替换的作者
 */

declare(strict_types=1);

namespace Dotenv\Repository\Adapter;

final class ReplacingWriter implements WriterInterface
{
    /**
     * The inner writer to use.
	 * 要使用的内写器
     *
     * @var \Dotenv\Repository\Adapter\WriterInterface
     */
    private $writer;

    /**
     * The inner reader to use.
	 * 要使用的内部阅读器
     *
     * @var \Dotenv\Repository\Adapter\ReaderInterface
     */
    private $reader;

    /**
     * The record of seen variables.
	 * 已见变量的记录
     *
     * @var array<string, string>
     */
    private $seen;

    /**
     * Create a new replacement writer instance.
	 * 创建一个新的替代写入器实例
     *
     * @param \Dotenv\Repository\Adapter\WriterInterface $writer
     * @param \Dotenv\Repository\Adapter\ReaderInterface $reader
     *
     * @return void
     */
    public function __construct(WriterInterface $writer, ReaderInterface $reader)
    {
        $this->writer = $writer;
        $this->reader = $reader;
        $this->seen = [];
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
        if ($this->exists($name)) {
            return $this->writer->write($name, $value);
        }

        // succeed if nothing to do
        return true;
    }

    /**
     * Delete an environment variable, if possible.
	 * 如果可能，请删除环境变量。
     *
     * @param non-empty-string $name
     *
     * @return bool
     */
    public function delete(string $name)
    {
        if ($this->exists($name)) {
            return $this->writer->delete($name);
        }

        // succeed if nothing to do
        return true;
    }

    /**
     * Does the given environment variable exist.
	 * 给定的环境变量是否存在。
     *
     * Returns true if it currently exists, or existed at any point in the past
     * that we are aware of.
	 * 如果当前存在，或在我们所知的过去某个时间点存在，则返回 true。
     *
     * @param non-empty-string $name
     *
     * @return bool
     */
    private function exists(string $name)
    {
        if (isset($this->seen[$name])) {
            return true;
        }

        if ($this->reader->read($name)->isDefined()) {
            $this->seen[$name] = '';

            return true;
        }

        return false;
    }
}
