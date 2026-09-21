<?php
/**
 * Illuminate，基础，测试，问题，与控制台交互
 */

namespace Illuminate\Foundation\Testing\Concerns;

use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Testing\PendingCommand;

trait InteractsWithConsole
{
    /**
     * Indicates if the console output should be mocked.
	 * 指示是否应该模拟控制台输出
     *
     * @var bool
     */
    public $mockConsoleOutput = true;

    /**
     * All of the expected output lines.
	 * 所有期望的输出行
     *
     * @var array
     */
    public $expectedOutput = [];

    /**
     * All of the expected text to be present in the output.
	 * 所有期望的文本都将出现在输出中
     *
     * @var array
     */
    public $expectedOutputSubstrings = [];

    /**
     * All of the output lines that aren't expected to be displayed.
	 * 所有不希望显示的输出行
     *
     * @var array
     */
    public $unexpectedOutput = [];

    /**
     * All of the text that is not expected to be present in the output.
	 * 所有不希望在输出中出现的文本
     *
     * @var array
     */
    public $unexpectedOutputSubstrings = [];

    /**
     * All of the expected output tables.
	 * 所有预期的输出表
     *
     * @var array
     */
    public $expectedTables = [];

    /**
     * All of the expected questions.
	 * 所有预期的问题
     *
     * @var array
     */
    public $expectedQuestions = [];

    /**
     * All of the expected choice questions.
	 * 所有的选择题
     *
     * @var array
     */
    public $expectedChoices = [];

    /**
     * Call artisan command and return code.
	 * 调用artisan命令并返回代码
     *
     * @param  string  $command
     * @param  array  $parameters
     * @return \Illuminate\Testing\PendingCommand|int
     */
    public function artisan($command, $parameters = [])
    {
        if (! $this->mockConsoleOutput) {
            return $this->app[Kernel::class]->call($command, $parameters);
        }

        return new PendingCommand($this, $this->app, $command, $parameters);
    }

    /**
     * Disable mocking the console output.
	 * 禁用模拟控制台输出
     *
     * @return $this
     */
    protected function withoutMockingConsoleOutput()
    {
        $this->mockConsoleOutput = false;

        $this->app->offsetUnset(OutputStyle::class);

        return $this;
    }
}
