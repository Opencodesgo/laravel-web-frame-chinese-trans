<?php
/**
 * Dotenv，存储，存储接口
 */

declare(strict_types=1);

namespace Dotenv\Store;

interface StoreInterface
{
    /**
     * Read the content of the environment file(s).
	 * 读取环境文件的内容
     *
     * @throws \Dotenv\Exception\InvalidEncodingException|\Dotenv\Exception\InvalidPathException
     *
     * @return string
     */
    public function read();
}
