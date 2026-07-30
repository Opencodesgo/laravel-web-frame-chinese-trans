<?php
/**
 * PhpParser，碰撞，契约，Writer
 */

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
 * This is the Collision Writer contract.
 * 这是Collision Writer合同。
 *
 * @author Nuno Maduro <enunomaduro@gmail.com>
 */
interface Writer
{
    /**
     * Ignores traces where the file string matches one
     * of the provided regex expressions.
	 * 忽略文件字符串与提供的正则表达式匹配的痕迹
     *
     * @param string[] $ignore the regex expressions
     *
     * @return \NunoMaduro\Collision\Contracts\Writer
     */
    public function ignoreFilesIn(array $ignore): Writer;

    /**
     * Declares whether or not the Writer should show the trace.
	 * 声明Writer是否应该显示跟踪
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
     */
    public function write(Inspector $inspector): void;

    /**
     * Sets the output.
     *
     * @return \NunoMaduro\Collision\Contracts\Writer
     */
    public function setOutput(OutputInterface $output): Writer;

    /**
     * Gets the output.
     */
    public function getOutput(): OutputInterface;
}
