<?php
/**
 * Dotenv，解析器，入口 
 */

declare(strict_types=1);

namespace Dotenv\Parser;

use PhpOption\Option;

final class Entry
{
    /**
     * The entry name.
	 * 入口名称
     *
     * @var string
     */
    private $name;

    /**
     * The entry value.
	 * 入口值
     *
     * @var \Dotenv\Parser\Value|null
     */
    private $value;

    /**
     * Create a new entry instance.
	 * 创建新的入口实例
     *
     * @param string                    $name
     * @param \Dotenv\Parser\Value|null $value
     *
     * @return void
     */
    public function __construct(string $name, ?Value $value = null)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Get the entry name.
	 * 获取条目名称
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the entry value.
	 * 获取条目值
     *
     * @return \PhpOption\Option<\Dotenv\Parser\Value>
     */
    public function getValue()
    {
        /** @var \PhpOption\Option<\Dotenv\Parser\Value> */
        return Option::fromValue($this->value);
    }
}
