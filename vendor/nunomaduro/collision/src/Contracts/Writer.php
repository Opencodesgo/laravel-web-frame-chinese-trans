<?php
/**
 * NunoMaduro，Collision，契约，作者
 */

declare(strict_types=1);

/**
 * This file is part of Collision.
 *
 * (c) Nuno Maduro <enunomaduro@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace NunoMaduro\Collision\Contracts;

use Symfony\Component\Console\Output\OutputInterface;
use Whoops\Exception\Inspector;

/**
 * @internal
 */
interface Writer
{
    /**
     * Ignores traces where the file string matches one
     * of the provided regex expressions.
	 * 忽略与文件字符串匹配的跟踪
     *
     * @param  string[]  $ignore the regex expressions
     * @return \NunoMaduro\Collision\Contracts\Writer
     */
    public function ignoreFilesIn(array $ignore): Writer;

    /**
     * Declares whether or not the Writer should show the trace.
	 * 声明Writer是否应该显示跟踪。
     *
     * @return \NunoMaduro\Collision\Contracts\Writer
     */
    public function showTrace(bool $show): Writer;

    /**
     * Declares whether or not the Writer should show the title.
	 * 声明Writer是否应该显示标题
     *
     * @return \NunoMaduro\Collision\Contracts\Writer
     */
    public function showTitle(bool $show): Writer;

    /**
     * Declares whether or not the Writer should show the editor.
	 * 声明Writer是否应该显示编辑器
     *
     * @return \NunoMaduro\Collision\Contracts\Writer
     */
    public function showEditor(bool $show): Writer;

    /**
     * Writes the details of the exception on the console.
	 * 在控制台中写入异常的详细信息
     */
    public function write(Inspector $inspector): void;

    /**
     * Sets the output.
	 * 设置输出
     *
     * @return \NunoMaduro\Collision\Contracts\Writer
     */
    public function setOutput(OutputInterface $output): Writer;

    /**
     * Gets the output.
	 * 得到输出
     */
    public function getOutput(): OutputInterface;
}
