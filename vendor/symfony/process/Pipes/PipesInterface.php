<?php
/**
 * Symfony，Component，Process，管道，管道接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Process\Pipes;

/**
 * PipesInterface manages descriptors and pipes for the use of proc_open.
 * PipesInterface管理proc_open使用的描述符和管道。
 *
 * @author Romain Neutron <imprec@gmail.com>
 *
 * @internal
 */
interface PipesInterface
{
    public const CHUNK_SIZE = 16384;

    /**
     * Returns an array of descriptors for the use of proc_open.
	 * 返回proc_open使用的描述符数组
     */
    public function getDescriptors(): array;

    /**
     * Returns an array of filenames indexed by their related stream in case these pipes use temporary files.
	 * 如果这些管道使用临时文件，则返回由其相关流索引的文件名数组。
     *
     * @return string[]
     */
    public function getFiles(): array;

    /**
     * Reads data in file handles and pipes.
	 * 读取文件句柄和管道中的数据
     *
     * @param bool $blocking Whether to use blocking calls or not
     * @param bool $close    Whether to close pipes if they've reached EOF
     *
     * @return string[] An array of read data indexed by their fd
     */
    public function readAndWrite(bool $blocking, bool $close = false): array;

    /**
     * Returns if the current state has open file handles or pipes.
	 * 如果当前状态有打开的文件句柄或管道，则返回。
     */
    public function areOpen(): bool;

    /**
     * Returns if pipes are able to read output.
	 * 如果管道能够读取输出，则返回。
     */
    public function haveReadSupport(): bool;

    /**
     * Closes file handles and pipes.
	 * 关闭文件句柄和管道
     */
    public function close(): void;
}
