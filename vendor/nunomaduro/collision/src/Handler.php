<?php
/**
 * NunoMaduro，Collision，处理者
 */

declare(strict_types=1);

namespace NunoMaduro\Collision;

use NunoMaduro\Collision\Contracts\Handler as HandlerContract;
use NunoMaduro\Collision\Contracts\Writer as WriterContract;
use Symfony\Component\Console\Output\OutputInterface;
use Whoops\Handler\Handler as AbstractHandler;

/**
 * @internal
 *
 * @see \Tests\Unit\HandlerTest
 */
final class Handler extends AbstractHandler implements HandlerContract
{
    /**
     * Holds an instance of the writer.
	 * 保存写入器的实例
     *
     * @var \NunoMaduro\Collision\Contracts\Writer
     */
    protected $writer;

    /**
     * Creates an instance of the Handler.
	 * 创建处理程序的实例
     */
    public function __construct(WriterContract $writer = null)
    {
        $this->writer = $writer ?: new Writer();
    }

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $this->writer->write($this->getInspector());

        return static::QUIT;
    }

    /**
     * {@inheritdoc}
     */
    public function setOutput(OutputInterface $output): HandlerContract
    {
        $this->writer->setOutput($output);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWriter(): WriterContract
    {
        return $this->writer;
    }
}
