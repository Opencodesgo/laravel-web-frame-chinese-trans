<?php
/**
 * Illuminate，基础，控制台，down 下载命令
 */

namespace Illuminate\Foundation\Console;

use App\Http\Middleware\PreventRequestsDuringMaintenance;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Foundation\Events\MaintenanceModeEnabled;
use Illuminate\Foundation\Exceptions\RegisterErrorViewPaths;
use Throwable;

class DownCommand extends Command
{
    /**
     * The console command signature.
	 * 控制台命令签名 down
     *
     * @var string
     */
    protected $signature = 'down {--redirect= : The path that users should be redirected to}
                                 {--render= : The view that should be prerendered for display during maintenance mode}
                                 {--retry= : The number of seconds after which the request may be retried}
                                 {--refresh= : The number of seconds after which the browser may refresh}
                                 {--secret= : The secret phrase that may be used to bypass maintenance mode}
                                 {--status=503 : The status code that should be used when returning the maintenance mode response}';

    /**
     * The console command description.
	 * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Put the application into maintenance / demo mode';		#将应用程序置于维护/演示模式

    /**
     * Execute the console command.
	 * 执行控制台命令
     *
     * @return int
     */
    public function handle()
    {
        try {
            if (is_file(storage_path('framework/down'))) {
                $this->comment('Application is already down.');

                return 0;
            }

            file_put_contents(
                storage_path('framework/down'),
                json_encode($this->getDownFilePayload(), JSON_PRETTY_PRINT)
            );

            file_put_contents(
                storage_path('framework/maintenance.php'),
                file_get_contents(__DIR__.'/stubs/maintenance-mode.stub')
            );

            $this->laravel->get('events')->dispatch(MaintenanceModeEnabled::class);

            $this->comment('Application is now in maintenance mode.');
        } catch (Exception $e) {
            $this->error('Failed to enter maintenance mode.');

            $this->error($e->getMessage());

            return 1;
        }
    }

    /**
     * Get the payload to be placed in the "down" file.
	 * 获取要放置在"down"文件中的有效负载
     *
     * @return array
     */
    protected function getDownFilePayload()
    {
        return [
            'except' => $this->excludedPaths(),
            'redirect' => $this->redirectPath(),
            'retry' => $this->getRetryTime(),
            'refresh' => $this->option('refresh'),
            'secret' => $this->option('secret'),
            'status' => (int) $this->option('status', 503),
            'template' => $this->option('render') ? $this->prerenderView() : null,
        ];
    }

    /**
     * Get the paths that should be excluded from maintenance mode.
	 * 获取应排除在维护模式之外的路径
     *
     * @return array
     */
    protected function excludedPaths()
    {
        try {
            return $this->laravel->make(PreventRequestsDuringMaintenance::class)->getExcludedPaths();
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Get the path that users should be redirected to.
	 * 获取用户应该重定向到的路径
     *
     * @return string
     */
    protected function redirectPath()
    {
        if ($this->option('redirect') && $this->option('redirect') !== '/') {
            return '/'.trim($this->option('redirect'), '/');
        }

        return $this->option('redirect');
    }

    /**
     * Prerender the specified view so that it can be rendered even before loading Composer.
	 * 预呈现指定的视图，以便它可以在加载Composer之前呈现。
     *
     * @return string
     */
    protected function prerenderView()
    {
        (new RegisterErrorViewPaths)();

        return view($this->option('render'), [
            'retryAfter' => $this->option('retry'),
        ])->render();
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
