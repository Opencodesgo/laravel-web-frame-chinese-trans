<?php declare(strict_types=1);

/**
 * Monolog，处理器，进程处理器
 */

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Handler;

use Monolog\Logger;

/**
 * Stores to STDIN of any process, specified by a command.
 * 存储任何进程的STDIN，由命令指定。
 *
 * Usage example:
 * <pre>
 * $log = new Logger('myLogger');
 * $log->pushHandler(new ProcessHandler('/usr/bin/php /var/www/monolog/someScript.php'));
 * </pre>
 *
 * @author Kolja Zuelsdorf <koljaz@web.de>
 */
class ProcessHandler extends AbstractProcessingHandler
{
    /**
     * Holds the process to receive data on its STDIN.
	 * 保存进程以接收其STDIN上的数据
     *
     * @var resource|bool|null
     */
    private $process;

    /**
     * @var string
     */
    private $command;

    /**
     * @var string|null
     */
    private $cwd;

    /**
     * @var resource[]
     */
    private $pipes = [];

    /**
     * @var array<int, string[]>
     */
    protected const DESCRIPTOR_SPEC = [
        0 => ['pipe', 'r'],  // STDIN is a pipe that the child will read from
        1 => ['pipe', 'w'],  // STDOUT is a pipe that the child will write to
        2 => ['pipe', 'w'],  // STDERR is a pipe to catch the any errors
    ];

    /**
     * @param  string                    $command Command for the process to start. Absolute paths are recommended,
     *                                            especially if you do not use the $cwd parameter.
     * @param  string|null               $cwd     "Current working directory" (CWD) for the process to be executed in.
     * @throws \InvalidArgumentException
     */
    public function __construct(string $command, $level = Logger::DEBUG, bool $bubble = true, ?string $cwd = null)
    {
        if ($command === '') {
            throw new \InvalidArgumentException('The command argument must be a non-empty string.');
        }
        if ($cwd === '') {
            throw new \InvalidArgumentException('The optional CWD argument must be a non-empty string or null.');
        }

        parent::__construct($level, $bubble);

        $this->command = $command;
        $this->cwd = $cwd;
    }

    /**
     * Writes the record down to the log of the implementing handler
	 * 将记录向下写入实现处理程序的日志
     *
     * @throws \UnexpectedValueException
     */
    protected function write(array $record): void
    {
        $this->ensureProcessIsStarted();

        $this->writeProcessInput($record['formatted']);

        $errors = $this->readProcessErrors();
        if (empty($errors) === false) {
            throw new \UnexpectedValueException(sprintf('Errors while writing to process: %s', $errors));
        }
    }

    /**
     * Makes sure that the process is actually started, and if not, starts it,
     * assigns the stream pipes, and handles startup errors, if any.
	 * 确保进程已经启动，如果没有，就启动它，分配流管道，并处理启动错误（如果有的话）。
     */
    private function ensureProcessIsStarted(): void
    {
        if (is_resource($this->process) === false) {
            $this->startProcess();

            $this->handleStartupErrors();
        }
    }

    /**
     * Starts the actual process and sets all streams to non-blocking.
	 * 启动实际进程并将所有流设置为非阻塞
     */
    private function startProcess(): void
    {
        $this->process = proc_open($this->command, static::DESCRIPTOR_SPEC, $this->pipes, $this->cwd);

        foreach ($this->pipes as $pipe) {
            stream_set_blocking($pipe, false);
        }
    }

    /**
     * Selects the STDERR stream, handles upcoming startup errors, and throws an exception, if any.
	 * 选择STDERR流，处理即将到来的启动错误，并抛出异常（如果有的话）。
     *
     * @throws \UnexpectedValueException
     */
    private function handleStartupErrors(): void
    {
        $selected = $this->selectErrorStream();
        if (false === $selected) {
            throw new \UnexpectedValueException('Something went wrong while selecting a stream.');
        }

        $errors = $this->readProcessErrors();

        if (is_resource($this->process) === false || empty($errors) === false) {
            throw new \UnexpectedValueException(
                sprintf('The process "%s" could not be opened: ' . $errors, $this->command)
            );
        }
    }

    /**
     * Selects the STDERR stream.
	 * 选择STDERR流
     *
     * @return int|bool
     */
    protected function selectErrorStream()
    {
        $empty = [];
        $errorPipes = [$this->pipes[2]];

        return stream_select($errorPipes, $empty, $empty, 1);
    }

    /**
     * Reads the errors of the process, if there are any.
	 * 读取进程的错误（如果有的话）
     *
     * @codeCoverageIgnore
     * @return string Empty string if there are no errors.
     */
    protected function readProcessErrors(): string
    {
        return (string) stream_get_contents($this->pipes[2]);
    }

    /**
     * Writes to the input stream of the opened process.
	 * 写入已打开进程的输入流
     *
     * @codeCoverageIgnore
     */
    protected function writeProcessInput(string $string): void
    {
        fwrite($this->pipes[0], $string);
    }

    /**
     * {@inheritDoc}
     */
    public function close(): void
    {
        if (is_resource($this->process)) {
            foreach ($this->pipes as $pipe) {
                fclose($pipe);
            }
            proc_close($this->process);
            $this->process = null;
        }
    }
}
