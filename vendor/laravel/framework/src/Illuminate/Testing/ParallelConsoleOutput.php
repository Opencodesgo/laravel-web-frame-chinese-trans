<?php
/**
 * Illuminate，测试，并行控制台输出
 */

namespace Illuminate\Testing;

use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class ParallelConsoleOutput extends ConsoleOutput
{
    /**
     * The original output instance.
	 * 原始输出实例
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * The output that should be ignored.
	 * 应该忽略的输出
     *
     * @var array
     */
    protected $ignore = [
        'Running phpunit in',
        'Configuration read from',
    ];

    /**
     * Create a new Parallel ConsoleOutput instance.
	 * 创建一个新的Parallel ConsoleOutput实例
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    public function __construct($output)
    {
        parent::__construct(
            $output->getVerbosity(),
            $output->isDecorated(),
            $output->getFormatter(),
        );

        $this->output = $output;
    }

    /**
     * Writes a message to the output.
	 * 将消息写入输出
     *
     * @param  string|iterable  $messages
     * @param  bool  $newline
     * @param  int  $options
     * @return void
     */
    public function write($messages, bool $newline = false, int $options = 0)
    {
        $messages = collect($messages)->filter(function ($message) {
            return ! Str::contains($message, $this->ignore);
        });

        $this->output->write($messages->toArray(), $newline, $options);
    }
}
