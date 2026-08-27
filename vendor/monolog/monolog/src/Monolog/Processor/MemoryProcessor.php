<?php declare(strict_types=1);

/**
 * Monolog，处理器，内存处理器
 */

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Processor;

/**
 * Some methods that are common for all memory processors
 * 所有内存处理器都通用的一些方法。
 *
 * @author Rob Jensen
 */
abstract class MemoryProcessor implements ProcessorInterface
{
    /**
     * @var bool If true, get the real size of memory allocated from system. Else, only the memory used by emalloc() is reported.
	 * 如果为true，则获取从系统分配的实际内存大小。否则，只报告emalloc()使用的内存。
     */
    protected $realUsage;

    /**
     * @var bool If true, then format memory size to human readable string (MB, KB, B depending on size)
     */
    protected $useFormatting;

    /**
     * @param bool $realUsage     Set this to true to get the real size of memory allocated from system.
     * @param bool $useFormatting If true, then format memory size to human readable string (MB, KB, B depending on size)
     */
    public function __construct(bool $realUsage = true, bool $useFormatting = true)
    {
        $this->realUsage = $realUsage;
        $this->useFormatting = $useFormatting;
    }

    /**
     * Formats bytes into a human readable string if $this->useFormatting is true, otherwise return $bytes as is
	 * 如果$this->useFormatting为true，则将字节格式化为人类可读的字符串，否则按原样返回$bytes。
     *
     * @param  int        $bytes
     * @return string|int Formatted string if $this->useFormatting is true, otherwise return $bytes as int
     */
    protected function formatBytes(int $bytes)
    {
        if (!$this->useFormatting) {
            return $bytes;
        }

        if ($bytes > 1024 * 1024) {
            return round($bytes / 1024 / 1024, 2).' MB';
        } elseif ($bytes > 1024) {
            return round($bytes / 1024, 2).' KB';
        }

        return $bytes . ' B';
    }
}
