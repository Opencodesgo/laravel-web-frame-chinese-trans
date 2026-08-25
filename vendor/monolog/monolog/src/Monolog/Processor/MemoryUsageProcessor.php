<?php declare(strict_types=1);

/**
 * Monolog，Processor，内存状态处理程序
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
 * Injects memory_get_usage in all records
 * 在所有记录中注入memory_get_use
 *
 * @see Monolog\Processor\MemoryProcessor::__construct() for options
 * @author Rob Jensen
 */
class MemoryUsageProcessor extends MemoryProcessor
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(array $record): array
    {
        $usage = memory_get_usage($this->realUsage);

        if ($this->useFormatting) {
            $usage = $this->formatBytes($usage);
        }

        $record['extra']['memory_usage'] = $usage;

        return $record;
    }
}
