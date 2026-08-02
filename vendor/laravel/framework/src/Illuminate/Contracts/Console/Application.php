<?php
/**
 * Illuminate，契约，控制台，应用
 */

namespace Illuminate\Contracts\Console;

interface Application
{
    /**
     * Run an Artisan console command by name.
	 * 按名称运行控制台命令
     *
     * @param  string  $command
     * @param  array  $parameters
     * @param  \Symfony\Component\Console\Output\OutputInterface|null  $outputBuffer
     * @return int
     */
    public function call($command, array $parameters = [], $outputBuffer = null);

    /**
     * Get the output from the last command.
	 * 从最后命令得到输出
     *
     * @return string
     */
    public function output();
}
