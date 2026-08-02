<?php
/**
 * NunoMaduro，Collision，契约，处理者
 */

declare(strict_types=1);

namespace NunoMaduro\Collision\Contracts;

use Symfony\Component\Console\Output\OutputInterface;
use Whoops\Handler\HandlerInterface;

/**
 * @internal
 */
interface Handler extends HandlerInterface
{
    /**
     * Sets the output.
	 * 设置输出
     *
     * @return \NunoMaduro\Collision\Contracts\Handler
     */
    public function setOutput(OutputInterface $output): Handler;

    /**
     * Returns the writer.
	 * 返回写入器
     *
     * @return \NunoMaduro\Collision\Contracts\Writer
     */
    public function getWriter(): Writer;
}
