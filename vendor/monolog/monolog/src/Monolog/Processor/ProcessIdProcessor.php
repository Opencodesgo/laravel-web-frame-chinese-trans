<?php declare(strict_types=1);

/**
 * Monolog，处理器，ProcessId 处理程序
 *

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
 * Adds value of getmypid into records
 * 将getmypid值添加到记录中
 *
 * @author Andreas Hörnicke
 */
class ProcessIdProcessor implements ProcessorInterface
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(array $record): array
    {
        $record['extra']['process_id'] = getmypid();

        return $record;
    }
}
