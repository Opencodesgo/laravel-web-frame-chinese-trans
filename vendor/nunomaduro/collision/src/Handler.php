<?php
/**
 * NunoMaduro，冲突，处理者
 */

/**
 * This file is part of Collision.
 *
 * (c) Nuno Maduro <enunomaduro@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace NunoMaduro\Collision;

use NunoMaduro\Collision\Contracts\Handler as HandlerContract;
use NunoMaduro\Collision\Contracts\Writer as WriterContract;
use Symfony\Component\Console\Output\OutputInterface;
use Whoops\Handler\Handler as AbstractHandler;

/**
 * This is an Collision Handler implementation.
 * 这是一个冲突处理程序实现。
 *
 * @author Nuno Maduro <enunomaduro@gmail.com>
 */
class Handler extends AbstractHandler implements HandlerContract
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
