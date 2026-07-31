<?php
/**
 * Dotenv，Repository，适配器，谨慎的作家
 */

declare(strict_types=1);

namespace Dotenv\Repository\Adapter;

final class GuardedWriter implements WriterInterface
{
    /**
     * The inner writer to use.
	 * 作者的使用
     *
     * @var \Dotenv\Repository\Adapter\WriterInterface
     */
    private $writer;

    /**
     * The variable name allow list.
	 * 变量名允许列表
     *
     * @var string[]
     */
    private $allowList;

    /**
     * Create a new guarded writer instance.
	 * 创建一个新的守卫者实例
     *
     * @param \Dotenv\Repository\Adapter\WriterInterface $writer
     * @param string[]                                   $allowList
     *
     * @return void
     */
    public function __construct(WriterInterface $writer, array $allowList)
    {
        $this->writer = $writer;
        $this->allowList = $allowList;
    }

    /**
     * Write to an environment variable, if possible.
	 * 如果可能的话,写入环境变量
     *
     * @param non-empty-string $name
     * @param string           $value
     *
     * @return bool
     */
    public function write(string $name, string $value)
    {
        // Don't set non-allowed variables
        if (!$this->isAllowed($name)) {
            return false;
        }

        // Set the value on the inner writer
        return $this->writer->write($name, $value);
    }

    /**
     * Delete an environment variable, if possible.
	 * 如果可能的话,删除一个环境变量
     *
     * @param non-empty-string $name
     *
     * @return bool
     */
    public function delete(string $name)
    {
        // Don't clear non-allowed variables
        if (!$this->isAllowed($name)) {
            return false;
        }

        // Set the value on the inner writer
        return $this->writer->delete($name);
    }

    /**
     * Determine if the given variable is allowed.
	 * 确定给定变量是否被允许
     *
     * @param non-empty-string $name
     *
     * @return bool
     */
    private function isAllowed(string $name)
    {
        return \in_array($name, $this->allowList, true);
    }
}
