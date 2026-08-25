<?php
/**
 * Symfony，Component，Console，样式，样式接口
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Style;

/**
 * Output style helpers.
 * 将消息格式化为文本块
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface StyleInterface
{
    /**
     * Formats a command title.
	 * 格式化一个命令标题
     */
    public function title(string $message);

    /**
     * Formats a section title.
	 * 格式化标题
     */
    public function section(string $message);

    /**
     * Formats a list.
	 * 格式化列表
     */
    public function listing(array $elements);

    /**
     * Formats informational text.
	 * 格式化信息文本
     *
     * @param string|array $message
     */
    public function text($message);

    /**
     * Formats a success result bar.
	 * 格式化一个成功的结果栏
     *
     * @param string|array $message
     */
    public function success($message);

    /**
     * Formats an error result bar.
	 * 格式化一个错误结果条
     *
     * @param string|array $message
     */
    public function error($message);

    /**
     * Formats an warning result bar.
	 * 格式化一个警告结果条
     *
     * @param string|array $message
     */
    public function warning($message);

    /**
     * Formats a note admonition.
	 * 格式化一个提示
     *
     * @param string|array $message
     */
    public function note($message);

    /**
     * Formats a caution admonition.
	 * 格式是一个谨慎的警告
     *
     * @param string|array $message
     */
    public function caution($message);

    /**
     * Formats a table.
	 * 格式化表格
     */
    public function table(array $headers, array $rows);

    /**
     * Asks a question.
     *
     * @return mixed
     */
    public function ask(string $question, ?string $default = null, ?callable $validator = null);

    /**
     * Asks a question with the user input hidden.
	 * 询问一个隐藏用户输入的问题
     *
     * @return mixed
     */
    public function askHidden(string $question, ?callable $validator = null);

    /**
     * Asks for confirmation.
	 * 要求确认
     *
     * @return bool
     */
    public function confirm(string $question, bool $default = true);

    /**
     * Asks a choice question.
	 * 问一个选择题
     *
     * @param string|int|null $default
     *
     * @return mixed
     */
    public function choice(string $question, array $choices, $default = null);

    /**
     * Add newline(s).
	 * 添加换行符(年代)
     */
    public function newLine(int $count = 1);

    /**
     * Starts the progress output.
	 * 启动进度输出
     */
    public function progressStart(int $max = 0);

    /**
     * Advances the progress output X steps.
	 * 将进度输出推进X步
     */
    public function progressAdvance(int $step = 1);

    /**
     * Finishes the progress output.
	 * 完成进度输出
     */
    public function progressFinish();
}
