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
	 * 指示是否应该嘲笑控制台输出
     *
     * @var bool
     */
    public $mockConsoleOutput = true;

    /**
     * All of the expected output lines.
	 * 所有预期的输出行
     *
     * @var array
     */
    public $expectedOutput = [];

    /**
     * All of the expected ouput tables.
	 * 所有预期的ouput表
     *
     * @var array
     */
    public $expectedTables = [];

    /**
     * All of the expected questions.
	 * 所有的预期问题
     *
     * @var array
     */
    public $expectedQuestions = [];

    /**
     * All of the expected choice questions.
	 * 所有预期的选择问题
     *
     * @var array
     */
    public $expectedChoices = [];

    /**
     * Call artisan command and return code.
	 * 调用artisan命令和返回代码
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
