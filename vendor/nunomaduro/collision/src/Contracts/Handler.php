<?php
/**
 * PhpParser，碰撞，契约，处理程序
 */

/*
 * This file is part of Collision.
 *
 * (c) Nuno Maduro <enunomaduro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NunoMaduro\Collision\Contracts;

use Symfony\Component\Console\Output\OutputInterface;
use Whoops\Handler\HandlerInterface;

/**
 * This is an Collision Handler contract.
 * 这是一个冲突处理程序契约。
 *
 * @author Nuno Maduro <enunomaduro@gmail.com>
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
     *
     * @return \NunoMaduro\Collision\Contracts\Writer
     */
    public function getWriter(): Writer;
}
