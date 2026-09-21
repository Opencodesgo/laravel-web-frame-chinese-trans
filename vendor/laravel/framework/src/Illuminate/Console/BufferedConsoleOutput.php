<?php
/**
 * Illuminate，控制台，缓冲控制台输出
 */

namespace Illuminate\Console;

use Symfony\Component\Console\Output\ConsoleOutput;

class BufferedConsoleOutput extends ConsoleOutput
{
    /**
     * The current buffer.
	 * 当前缓冲
     *
     * @var string
     */
    protected $buffer = '';

    /**
     * Empties the buffer and returns its content.
	 * 清空缓冲区并返回其内容
     *
     * @return string
     */
    public function fetch()
    {
        return tap($this->buffer, function () {
            $this->buffer = '';
        });
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    protected function doWrite(string $message, bool $newline)
    {
        $this->buffer .= $message;

        if ($newline) {
            $this->buffer .= \PHP_EOL;
        }

        return parent::doWrite($message, $newline);
    }
}
