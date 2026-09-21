<?php declare(strict_types=1);

/**
 * Monolog，处理器，处理器接口
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
 * An optional interface to allow labelling Monolog processors.
 * 允许标记独白处理器的可选接口。
 *
 * @author Nicolas Grekas <p@tchwork.com>
 *
 * @phpstan-import-type Record from \Monolog\Logger
 */
interface ProcessorInterface
{
    /**
     * @return array The processed record
     *
     * @phpstan-param  Record $record
     * @phpstan-return Record
     */
    public function __invoke(array $record);
}
