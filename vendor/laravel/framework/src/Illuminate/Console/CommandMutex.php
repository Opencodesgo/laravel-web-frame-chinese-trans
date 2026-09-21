<?php
/**
 * Illuminate，控制台，命令互斥
 */

namespace Illuminate\Console;

interface CommandMutex
{
    /**
     * Attempt to obtain a command mutex for the given command.
	 * 尝试获取给定命令的命令互斥锁
     *
     * @param  \Illuminate\Console\Command  $command
     * @return bool
     */
    public function create($command);

    /**
     * Determine if a command mutex exists for the given command.
	 * 确定给定命令是否存在命令互斥锁
     *
     * @param  \Illuminate\Console\Command  $command
     * @return bool
     */
    public function exists($command);

    /**
     * Release the mutex for the given command.
	 * 释放给定命令的互斥
     *
     * @param  \Illuminate\Console\Command  $command
     * @return bool
     */
    public function forget($command);
}
