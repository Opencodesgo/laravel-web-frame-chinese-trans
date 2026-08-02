<?php
/**
 * Dotenv，存储，存储接口
 */

namespace Dotenv\Store;

interface StoreInterface
{
    /**
     * Read the content of the environment file(s).
	 * 读取环境文件的内容
     *
     * @throws \Dotenv\Exception\InvalidPathException
     *
     * @return string
     */
    public function read();
}
