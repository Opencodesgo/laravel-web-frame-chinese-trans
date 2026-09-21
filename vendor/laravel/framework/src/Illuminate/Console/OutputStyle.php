<?php
/**
 * Illuminate，控制台，输出样式
 */

namespace Illuminate\Console;

use Illuminate\Console\Contracts\NewLineAware;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OutputStyle extends SymfonyStyle implements NewLineAware
{
    /**
     * The output instance.
	 * 输出实例
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * If the last output written wrote a new line.
	 * 如果最后一个输出写入，则写入新行。
     *
     * @var bool
     */
    protected $newLineWritten = false;

    /**
     * Create a new Console OutputStyle instance.
	 * 创建一个新的控制台OutputStyle实例
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        parent::__construct($input, $output);
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function write(string|iterable $messages, bool $newline = false, int $options = 0)
    {
        $this->newLineWritten = $newline;

        parent::write($messages, $newline, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function writeln(string|iterable $messages, int $type = self::OUTPUT_NORMAL)
    {
        $this->newLineWritten = true;

        parent::writeln($messages, $type);
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function newLine(int $count = 1)
    {
        $this->newLineWritten = $count > 0;

        parent::newLine($count);
    }

    /**
     * {@inheritdoc}
     */
    public function newLineWritten()
    {
        if ($this->output instanceof static && $this->output->newLineWritten()) {
            return true;
        }

        return $this->newLineWritten;
    }

    /**
     * Returns whether verbosity is quiet (-q).
	 * 返回verbose是否为quiet （-q）
     *
     * @return bool
     */
    public function isQuiet(): bool
    {
        return $this->output->isQuiet();
    }

    /**
     * Returns whether verbosity is verbose (-v).
	 * 返回verbose是否为verbose （-v）
     *
     * @return bool
     */
    public function isVerbose(): bool
    {
        return $this->output->isVerbose();
    }

    /**
     * Returns whether verbosity is very verbose (-vv).
     *
     * @return bool
     */
    public function isVeryVerbose(): bool
    {
        return $this->output->isVeryVerbose();
    }

    /**
     * Returns whether verbosity is debug (-vvv).
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->output->isDebug();
    }

    /**
     * Get the underlying Symfony output implementation.
	 * 获取底层Symfony输出实现
     *
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }
}
