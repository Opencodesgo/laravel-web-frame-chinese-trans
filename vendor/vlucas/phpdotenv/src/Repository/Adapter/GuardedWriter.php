<?php
/**
 * Dotenv，资源库，适配器，Guarded 作者
 */

declare(strict_types=1);

namespace Dotenv\Repository\Adapter;

final class GuardedWriter implements WriterInterface
{
    /**
     * The inner writer to use.
	 * 要使用的内写器
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
	 * 创建一个新的受保护写入器实例
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
	 * 如果可能的话，写入环境变量。
     *
     * @param non-empty-string $name
     * @param string           $value
     *
     * @return bool
     */
    public function write(string $name, string $value)
    {
        // Don't set non-allowed variables
		// 不要设置不允许的变量
        if (!$this->isAllowed($name)) {
            return false;
        }

        // Set the value on the inner writer
		// 在内部写入器上设置该值
        return $this->writer->write($name, $value);
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
        // Don't clear non-allowed variables
        if (!$this->isAllowed($name)) {
            return false;
        }

        // Set the value on the inner writer
        return $this->writer->delete($name);
    }

    /**
     * Determine if the given variable is allowed.
	 * 确定是否允许给定变量
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
