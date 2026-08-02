<?php
/**
 * Dotenv，存储，字符串存储
 */

namespace Dotenv\Store;

final class StringStore implements StoreInterface
{
    /**
     * The file content.
	 * 文件内容
     *
     * @var string
     */
    private $content;

    /**
     * Create a new string store instance.
	 * 创建一个新的字符串存储实例
     *
     * @param string $content
     *
     * @return void
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * Read the content of the environment file(s).
	 * 读取环境文件的内容
     *
     * @return string
     */
    public function read()
    {
        return $this->content;
    }
}
