<?php
/**
 * Illuminate，队列，控制台，queue:failed 列表失败命令
 */

namespace Illuminate\Queue\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'queue:failed')]
class ListFailedCommand extends Command
{
    /**
     * The console command name.
	 * 控制台命令名称
     *
     * @var string
     */
    protected $name = 'queue:failed';

    /**
     * The name of the console command.
	 * 控制台命令名称
     *
     * This name is used to identify the command during lazy loading.
	 * 此名称用于在惰性加载期间识别命令
     *
     * @var string|null
     *
     * @deprecated
     */
    protected static $defaultName = 'queue:failed';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'List all of the failed queue jobs';

    /**
     * The table headers for the command.
	 * 命令的表头
     *
     * @var string[]
     */
    protected $headers = ['ID', 'Connection', 'Queue', 'Class', 'Failed At'];

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @return void
     */
    public function handle()
    {
        if (count($jobs = $this->getFailedJobs()) === 0) {
            return $this->components->info('No failed jobs found.');
        }

        $this->newLine();
        $this->displayFailedJobs($jobs);
        $this->newLine();
    }

    /**
     * Compile the failed jobs into a displayable format.
	 * 将失败的作业编译为可显示的格式
     *
     * @return array
     */
    protected function getFailedJobs()
    {
        $failed = $this->laravel['queue.failer']->all();

        return collect($failed)->map(function ($failed) {
            return $this->parseFailedJob((array) $failed);
        })->filter()->all();
    }

    /**
     * Parse the failed job row.
	 * 解析失败的作业行
     *
     * @param  array  $failed
     * @return array
     */
    protected function parseFailedJob(array $failed)
    {
        $row = array_values(Arr::except($failed, ['payload', 'exception']));

        array_splice($row, 3, 0, $this->extractJobName($failed['payload']) ?: '');

        return $row;
    }

    /**
     * Extract the failed job name from payload.
	 * 从有效负载中提取失败的作业名称
     *
     * @param  string  $payload
     * @return string|null
     */
    private function extractJobName($payload)
    {
        $payload = json_decode($payload, true);

        if ($payload && (! isset($payload['data']['command']))) {
            return $payload['job'] ?? null;
        } elseif ($payload && isset($payload['data']['command'])) {
            return $this->matchJobName($payload);
        }
    }

    /**
     * Match the job name from the payload.
	 * 从有效负载匹配作业名称
     *
     * @param  array  $payload
     * @return string|null
     */
    protected function matchJobName($payload)
    {
        preg_match('/"([^"]+)"/', $payload['data']['command'], $matches);

        return $matches[1] ?? $payload['job'] ?? null;
    }

    /**
     * Display the failed jobs in the console.
	 * 在控制台中显示失败的作业
     *
     * @param  array  $jobs
     * @return void
     */
    protected function displayFailedJobs(array $jobs)
    {
        collect($jobs)->each(
            fn ($job) => $this->components->twoColumnDetail(
                sprintf('<fg=gray>%s</> %s</>', $job[4], $job[0]),
                sprintf('<fg=gray>%s@%s</> %s', $job[1], $job[2], $job[3])
            ),
        );
    }
}
