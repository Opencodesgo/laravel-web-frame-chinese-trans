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
 * 输出样式帮助器。
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface StyleInterface
{
    /**
     * Formats a command title.
	 * 格式化命令标题
     *
     * @return void
     */
    public function title(string $message);

    /**
     * Formats a section title.
	 * 格式化节标题
     *
     * @return void
     */
    public function section(string $message);

    /**
     * Formats a list.
	 * 格式化列表
     *
     * @return void
     */
    public function listing(array $elements);

    /**
     * Formats informational text.
	 * 格式化信息文本
     *
     * @return void
     */
    public function text(string|array $message);

    /**
     * Formats a success result bar.
	 * 格式化成功结果栏
     *
     * @return void
     */
    public function success(string|array $message);

    /**
     * Formats an error result bar.
	 * 格式化错误结果栏
     *
     * @return void
     */
    public function error(string|array $message);

    /**
     * Formats an warning result bar.
	 * 格式化警告结果栏
     *
     * @return void
     */
    public function warning(string|array $message);

    /**
     * Formats a note admonition.
	 * 格式化一个提示
     *
     * @return void
     */
    public function note(string|array $message);

    /**
     * Formats a caution admonition.
	 * 格式是一个谨慎的警告
     *
     * @return void
     */
    public function caution(string|array $message);

    /**
     * Formats a table.
	 * 格式化表格
     *
     * @return void
     */
    public function table(array $headers, array $rows);

    /**
     * Asks a question.
	 * 问一个问题
     */
    public function ask(string $question, ?string $default = null, ?callable $validator = null): mixed;

    /**
     * Asks a question with the user input hidden.
	 * 询问一个隐藏用户输入的问题
     */
    public function askHidden(string $question, ?callable $validator = null): mixed;

    /**
     * Asks for confirmation.
	 * 要求确认
     */
    public function confirm(string $question, bool $default = true): bool;

    /**
     * Asks a choice question.
	 * 问一个选择题
     */
    public function choice(string $question, array $choices, mixed $default = null): mixed;

    /**
     * Add newline(s).
	 * 添加换行符(年代)
     *
     * @return void
     */
    public function newLine(int $count = 1);

    /**
     * Starts the progress output.
	 * 启动进度输出
     *
     * @return void
     */
    public function progressStart(int $max = 0);

    /**
     * Advances the progress output X steps.
	 * 将进度输出推进X步
     *
     * @return void
     */
    public function progressAdvance(int $step = 1);

    /**
     * Finishes the progress output.
	 * 完成进度输出
     *
     * @return void
     */
    public function progressFinish();
}
