<?php
/**
 * Dotenv，解析器，解析器接口 
 */

declare(strict_types=1);

namespace Dotenv\Parser;

interface ParserInterface
{
    /**
     * Parse content into an entry array.
	 * 将内容解析为条目数组
     *
     * @param string $content
     *
     * @throws \Dotenv\Exception\InvalidFileException
     *
     * @return \Dotenv\Parser\Entry[]
     */
    public function parse(string $content);
}
