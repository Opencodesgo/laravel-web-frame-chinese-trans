<?php
/**
 * Illuminate，基础，控制台，down 下线命令
 */

namespace Illuminate\Foundation\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\InteractsWithTime;

class DownCommand extends Command
{
    use InteractsWithTime;

    /**
     * The console command signature.
	 * 控制台命令签名
     *
     * @var string
     */
    protected $signature = 'down {--message= : The message for the maintenance mode}
                                 {--retry= : The number of seconds after which the request may be retried}
                                 {--allow=* : IP or networks allowed to access the application while in maintenance mode}';

    /**
     * The console command description.
	 * 将应用置于维护模式
     *
     * @var string
     */
    protected $description = 'Put the application into maintenance mode';

    /**
     * Execute the console command.
	 * 执行命令
     *
     * @return int
     */
    public function handle()
    {
        try {
			// 文件storage/framework/down
            if (file_exists(storage_path('framework/down'))) {
                $this->comment('Application is already down.');

                return true;
            }

            file_put_contents(storage_path('framework/down'),
                              json_encode($this->getDownFilePayload(),
                              JSON_PRETTY_PRINT));

            $this->comment('Application is now in maintenance mode.');
        } catch (Exception $e) {
            $this->error('Failed to enter maintenance mode.');

            $this->error($e->getMessage());

            return 1;
        }
    }

    /**
     * Get the payload to be placed in the "down" file.
	 * 获取要放置在“down”文件中的有效负载
     *
     * @return array
     */
    protected function getDownFilePayload()
    {
        return [
            'time' => $this->currentTime(),
            'message' => $this->option('message'),
            'retry' => $this->getRetryTime(),
            'allowed' => $this->option('allow'),
        ];
    }

    /**
     * Get the number of seconds the client should wait before retrying their request.
	 * 获取客户端在重试请求之前应该等待的秒数
     *
     * @return int|null
     */
    protected function getRetryTime()
    {
        $retry = $this->option('retry');

        return is_numeric($retry) && $retry > 0 ? (int) $retry : null;
    }
}
