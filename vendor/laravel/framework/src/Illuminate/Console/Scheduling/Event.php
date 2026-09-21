<?php
/**
 * Illuminate，控制台，线程调度，事件
 */

namespace Illuminate\Console\Scheduling;

use Closure;
use Cron\CronExpression;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\TransferException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Reflector;
use Illuminate\Support\Stringable;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Traits\ReflectsClosures;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\Process\Process;
use Throwable;

class Event
{
    use Macroable, ManagesFrequencies, ReflectsClosures;

    /**
     * The command string.
	 * 命令字符串
     *
     * @var string|null
     */
    public $command;

    /**
     * The cron expression representing the event's frequency.
	 * 表示事件频率的cron表达式
     *
     * @var string
     */
    public $expression = '* * * * *';

    /**
     * The timezone the date should be evaluated on.
	 * 应该对日期进行评估的时区
     *
     * @var \DateTimeZone|string
     */
    public $timezone;

    /**
     * The user the command should run as.
	 * 命令应该作为用户运行
     *
     * @var string|null
     */
    public $user;

    /**
     * The list of environments the command should run under.
	 * 命令应该运行的环境列表
     *
     * @var array
     */
    public $environments = [];

    /**
     * Indicates if the command should run in maintenance mode.
	 * 指示该命令是否在维护模式下运
     *
     * @var bool
     */
    public $evenInMaintenanceMode = false;

    /**
     * Indicates if the command should not overlap itself.
	 * 指示命令是否不应该重叠
     *
     * @var bool
     */
    public $withoutOverlapping = false;

    /**
     * Indicates if the command should only be allowed to run on one server for each cron expression.
	 * 指示是否应该只允许对每个cron表达式在一台服务器上运行该命令
     *
     * @var bool
     */
    public $onOneServer = false;

    /**
     * The number of minutes the mutex should be valid.
	 * 互斥锁有效的分钟数
     *
     * @var int
     */
    public $expiresAt = 1440;

    /**
     * Indicates if the command should run in the background.
	 * 指示该命令是否应该在后台运行
     *
     * @var bool
     */
    public $runInBackground = false;

    /**
     * The array of filter callbacks.
	 * 过滤器回调函数数组
     *
     * @var array
     */
    protected $filters = [];

    /**
     * The array of reject callbacks.
	 * 拒绝回调的数组
     *
     * @var array
     */
    protected $rejects = [];

    /**
     * The location that output should be sent to.
	 * 输出应该发送到的位置
     *
     * @var string
     */
    public $output = '/dev/null';

    /**
     * Indicates whether output should be appended.
	 * 指示是否应追加输出
     *
     * @var bool
     */
    public $shouldAppendOutput = false;

    /**
     * The array of callbacks to be run before the event is started.
	 * 在事件开始之前要运行的回调函数数组
     *
     * @var array
     */
    protected $beforeCallbacks = [];

    /**
     * The array of callbacks to be run after the event is finished.
	 * 事件完成后要运行的回调函数数组
     *
     * @var array
     */
    protected $afterCallbacks = [];

    /**
     * The human readable description of the event.
	 * 人类可读的事件描述
     *
     * @var string|null
     */
    public $description;

    /**
     * The event mutex implementation.
	 * 事件互斥锁的实现
     *
     * @var \Illuminate\Console\Scheduling\EventMutex
     */
    public $mutex;

    /**
     * The mutex name resolver callback.
	 * 互斥锁名称解析器回调
     *
     * @var \Closure|null
     */
    public $mutexNameResolver;

    /**
     * The exit status code of the command.
	 * 命令的退出状态码
     *
     * @var int|null
     */
    public $exitCode;

    /**
     * Create a new event instance.
	 * 创建一个新的事件实例
     *
     * @param  \Illuminate\Console\Scheduling\EventMutex  $mutex
     * @param  string  $command
     * @param  \DateTimeZone|string|null  $timezone
     * @return void
     */
    public function __construct(EventMutex $mutex, $command, $timezone = null)
    {
        $this->mutex = $mutex;
        $this->command = $command;
        $this->timezone = $timezone;

        $this->output = $this->getDefaultOutput();
    }

    /**
     * Get the default output depending on the OS.
	 * 根据操作系统获取默认输出
     *
     * @return string
     */
    public function getDefaultOutput()
    {
        return (DIRECTORY_SEPARATOR === '\\') ? 'NUL' : '/dev/null';
    }

    /**
     * Run the given event.
	 * 运行给定的事件
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return void
     *
     * @throws \Throwable
     */
    public function run(Container $container)
    {
        if ($this->shouldSkipDueToOverlapping()) {
            return;
        }

        $exitCode = $this->start($container);

        if (! $this->runInBackground) {
            $this->finish($container, $exitCode);
        }
    }

    /**
     * Determine if the event should skip because another process is overlapping.
	 * 确定是否应该跳过事件，因为另一个进程正在重叠。
     *
     * @return bool
     */
    public function shouldSkipDueToOverlapping()
    {
        return $this->withoutOverlapping && ! $this->mutex->create($this);
    }

    /**
     * Run the command process.
	 * 执行命令流程
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return int
     *
     * @throws \Throwable
     */
    protected function start($container)
    {
        try {
            $this->callBeforeCallbacks($container);

            return $this->execute($container);
        } catch (Throwable $exception) {
            $this->removeMutex();

            throw $exception;
        }
    }

    /**
     * Run the command process.
	 * 执行命令流程
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return int
     */
    protected function execute($container)
    {
        return Process::fromShellCommandline(
            $this->buildCommand(), base_path(), null, null, null
        )->run(
            laravel_cloud()
                ? fn ($type, $line) => fwrite($type === 'out' ? STDOUT : STDERR, $line)
                : fn () => true
        );
    }

    /**
     * Mark the command process as finished and run callbacks/cleanup.
	 * 将命令进程标记为已完成，并运行回调/清理。
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @param  int  $exitCode
     * @return void
     */
    public function finish(Container $container, $exitCode)
    {
        $this->exitCode = (int) $exitCode;

        try {
            $this->callAfterCallbacks($container);
        } finally {
            $this->removeMutex();
        }
    }

    /**
     * Call all of the "before" callbacks for the event.
	 * 调用事件的所有"before"回调
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return void
     */
    public function callBeforeCallbacks(Container $container)
    {
        foreach ($this->beforeCallbacks as $callback) {
            $container->call($callback);
        }
    }

    /**
     * Call all of the "after" callbacks for the event.
	 * 调用事件的所有"after"回调
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return void
     */
    public function callAfterCallbacks(Container $container)
    {
        foreach ($this->afterCallbacks as $callback) {
            $container->call($callback);
        }
    }

    /**
     * Build the command string.
	 * 构建命令字符串
     *
     * @return string
     */
    public function buildCommand()
    {
        return (new CommandBuilder)->buildCommand($this);
    }

    /**
     * Determine if the given event should run based on the Cron expression.
	 * 确定给定的事件是否应该基于Cron表达式运行
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return bool
     */
    public function isDue($app)
    {
        if (! $this->runsInMaintenanceMode() && $app->isDownForMaintenance()) {
            return false;
        }

        return $this->expressionPasses() &&
               $this->runsInEnvironment($app->environment());
    }

    /**
     * Determine if the event runs in maintenance mode.
	 * 确定事件是否在维护模式下运行
     *
     * @return bool
     */
    public function runsInMaintenanceMode()
    {
        return $this->evenInMaintenanceMode;
    }

    /**
     * Determine if the Cron expression passes.
	 * 确定Cron表达式是否通过
     *
     * @return bool
     */
    protected function expressionPasses()
    {
        $date = Date::now();

        if ($this->timezone) {
            $date = $date->setTimezone($this->timezone);
        }

        return (new CronExpression($this->expression))->isDue($date->toDateTimeString());
    }

    /**
     * Determine if the event runs in the given environment.
	 * 确定事件是否在给定环境中运行
     *
     * @param  string  $environment
     * @return bool
     */
    public function runsInEnvironment($environment)
    {
        return empty($this->environments) || in_array($environment, $this->environments);
    }

    /**
     * Determine if the filters pass for the event.
	 * 确定筛选器是否通过该事件
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return bool
     */
    public function filtersPass($app)
    {
        foreach ($this->filters as $callback) {
            if (! $app->call($callback)) {
                return false;
            }
        }

        foreach ($this->rejects as $callback) {
            if ($app->call($callback)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Ensure that the output is stored on disk in a log file.
	 * 确保输出以日志文件的形式存储在磁盘上
     *
     * @return $this
     */
    public function storeOutput()
    {
        $this->ensureOutputIsBeingCaptured();

        return $this;
    }

    /**
     * Send the output of the command to a given location.
	 * 将命令的输出发送到给定位置
     *
     * @param  string  $location
     * @param  bool  $append
     * @return $this
     */
    public function sendOutputTo($location, $append = false)
    {
        $this->output = $location;

        $this->shouldAppendOutput = $append;

        return $this;
    }

    /**
     * Append the output of the command to a given location.
	 * 将命令的输出附加到给定位置
     *
     * @param  string  $location
     * @return $this
     */
    public function appendOutputTo($location)
    {
        return $this->sendOutputTo($location, true);
    }

    /**
     * E-mail the results of the scheduled operation.
	 * 通过电子邮件发送预定操作的结果
     *
     * @param  array|mixed  $addresses
     * @param  bool  $onlyIfOutputExists
     * @return $this
     *
     * @throws \LogicException
     */
    public function emailOutputTo($addresses, $onlyIfOutputExists = false)
    {
        $this->ensureOutputIsBeingCaptured();

        $addresses = Arr::wrap($addresses);

        return $this->then(function (Mailer $mailer) use ($addresses, $onlyIfOutputExists) {
            $this->emailOutput($mailer, $addresses, $onlyIfOutputExists);
        });
    }

    /**
     * E-mail the results of the scheduled operation if it produces output.
	 * 如果计划操作产生输出，则通过电子邮件发送该操作的结果。
     *
     * @param  array|mixed  $addresses
     * @return $this
     *
     * @throws \LogicException
     */
    public function emailWrittenOutputTo($addresses)
    {
        return $this->emailOutputTo($addresses, true);
    }

    /**
     * E-mail the results of the scheduled operation if it fails.
	 * 如果计划操作失败，则通过电子邮件发送其结果。
     *
     * @param  array|mixed  $addresses
     * @return $this
     */
    public function emailOutputOnFailure($addresses)
    {
        $this->ensureOutputIsBeingCaptured();

        $addresses = Arr::wrap($addresses);

        return $this->onFailure(function (Mailer $mailer) use ($addresses) {
            $this->emailOutput($mailer, $addresses, false);
        });
    }

    /**
     * Ensure that the command output is being captured.
	 * 确保正在捕获命令输出
     *
     * @return void
     */
    protected function ensureOutputIsBeingCaptured()
    {
        if (is_null($this->output) || $this->output == $this->getDefaultOutput()) {
            $this->sendOutputTo(storage_path('logs/schedule-'.sha1($this->mutexName()).'.log'));
        }
    }

    /**
     * E-mail the output of the event to the recipients.
	 * 将事件的输出通过电子邮件发送给收件人
     *
     * @param  \Illuminate\Contracts\Mail\Mailer  $mailer
     * @param  array  $addresses
     * @param  bool  $onlyIfOutputExists
     * @return void
     */
    protected function emailOutput(Mailer $mailer, $addresses, $onlyIfOutputExists = false)
    {
        $text = is_file($this->output) ? file_get_contents($this->output) : '';

        if ($onlyIfOutputExists && empty($text)) {
            return;
        }

        $mailer->raw($text, function ($m) use ($addresses) {
            $m->to($addresses)->subject($this->getEmailSubject());
        });
    }

    /**
     * Get the e-mail subject line for output results.
	 * 获取输出结果的电子邮件主题行
     *
     * @return string
     */
    protected function getEmailSubject()
    {
        if ($this->description) {
            return $this->description;
        }

        return "Scheduled Job Output For [{$this->command}]";
    }

    /**
     * Register a callback to ping a given URL before the job runs.
	 * 注册一个回调，以便在作业运行之前ping给定的URL。
     *
     * @param  string  $url
     * @return $this
     */
    public function pingBefore($url)
    {
        return $this->before($this->pingCallback($url));
    }

    /**
     * Register a callback to ping a given URL before the job runs if the given condition is true.
	 * 注册一个回调函数，如果给定的条件为真，则在作业运行之前ping给定的URL。
     *
     * @param  bool  $value
     * @param  string  $url
     * @return $this
     */
    public function pingBeforeIf($value, $url)
    {
        return $value ? $this->pingBefore($url) : $this;
    }

    /**
     * Register a callback to ping a given URL after the job runs.
	 * 注册一个回调函数，以便在作业运行后ping给定的URL。
     *
     * @param  string  $url
     * @return $this
     */
    public function thenPing($url)
    {
        return $this->then($this->pingCallback($url));
    }

    /**
     * Register a callback to ping a given URL after the job runs if the given condition is true.
	 * 如果给定的条件为真，则在作业运行后注册一个回调来ping给定的URL。
     *
     * @param  bool  $value
     * @param  string  $url
     * @return $this
     */
    public function thenPingIf($value, $url)
    {
        return $value ? $this->thenPing($url) : $this;
    }

    /**
     * Register a callback to ping a given URL if the operation succeeds.
	 * 如果操作成功，注册一个回调来ping给定的URL。
     *
     * @param  string  $url
     * @return $this
     */
    public function pingOnSuccess($url)
    {
        return $this->onSuccess($this->pingCallback($url));
    }

    /**
     * Register a callback to ping a given URL if the operation fails.
	 * 如果操作失败，注册一个回调来ping给定的URL。
     *
     * @param  string  $url
     * @return $this
     */
    public function pingOnFailure($url)
    {
        return $this->onFailure($this->pingCallback($url));
    }

    /**
     * Get the callback that pings the given URL.
	 * 获取ping给定URL的回调
     *
     * @param  string  $url
     * @return \Closure
     */
    protected function pingCallback($url)
    {
        return function (Container $container, HttpClient $http) use ($url) {
            try {
                $http->request('GET', $url);
            } catch (ClientExceptionInterface|TransferException $e) {
                $container->make(ExceptionHandler::class)->report($e);
            }
        };
    }

    /**
     * State that the command should run in the background.
	 * 声明该命令应该在后台运行
     *
     * @return $this
     */
    public function runInBackground()
    {
        $this->runInBackground = true;

        return $this;
    }

    /**
     * Set which user the command should run as.
	 * 设置命令应该作为哪个用户运行
     *
     * @param  string  $user
     * @return $this
     */
    public function user($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Limit the environments the command should run in.
	 * 限制命令应该运行的环境
     *
     * @param  array|mixed  $environments
     * @return $this
     */
    public function environments($environments)
    {
        $this->environments = is_array($environments) ? $environments : func_get_args();

        return $this;
    }

    /**
     * State that the command should run even in maintenance mode.
	 * 说明该命令即使在维护模式下也应该运行
     *
     * @return $this
     */
    public function evenInMaintenanceMode()
    {
        $this->evenInMaintenanceMode = true;

        return $this;
    }

    /**
     * Do not allow the event to overlap each other.
	 * 不要让事件相互重叠
     *
     * The expiration time of the underlying cache lock may be specified in minutes.
     *
     * @param  int  $expiresAt
     * @return $this
     */
    public function withoutOverlapping($expiresAt = 1440)
    {
        $this->withoutOverlapping = true;

        $this->expiresAt = $expiresAt;

        return $this->skip(function () {
            return $this->mutex->exists($this);
        });
    }

    /**
     * Allow the event to only run on one server for each cron expression.
	 * 对于每个cron表达式，允许事件仅在一台服务器上运行。
     *
     * @return $this
     */
    public function onOneServer()
    {
        $this->onOneServer = true;

        return $this;
    }

    /**
     * Register a callback to further filter the schedule.
	 * 注册回调以进一步筛选计划
     *
     * @param  \Closure|bool  $callback
     * @return $this
     */
    public function when($callback)
    {
        $this->filters[] = Reflector::isCallable($callback) ? $callback : function () use ($callback) {
            return $callback;
        };

        return $this;
    }

    /**
     * Register a callback to further filter the schedule.
	 * 注册回调以进一步筛选计划
     *
     * @param  \Closure|bool  $callback
     * @return $this
     */
    public function skip($callback)
    {
        $this->rejects[] = Reflector::isCallable($callback) ? $callback : function () use ($callback) {
            return $callback;
        };

        return $this;
    }

    /**
     * Register a callback to be called before the operation.
	 * 在操作之前注册一个回调函数
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function before(Closure $callback)
    {
        $this->beforeCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a callback to be called after the operation.
	 * 注册一个在操作之后调用的回调函数
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function after(Closure $callback)
    {
        return $this->then($callback);
    }

    /**
     * Register a callback to be called after the operation.
	 * 注册一个在操作之后调用的回调函数
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function then(Closure $callback)
    {
        $parameters = $this->closureParameterTypes($callback);

        if (Arr::get($parameters, 'output') === Stringable::class) {
            return $this->thenWithOutput($callback);
        }

        $this->afterCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a callback that uses the output after the job runs.
	 * 注册一个回调函数，在作业运行后使用输出。
     *
     * @param  \Closure  $callback
     * @param  bool  $onlyIfOutputExists
     * @return $this
     */
    public function thenWithOutput(Closure $callback, $onlyIfOutputExists = false)
    {
        $this->ensureOutputIsBeingCaptured();

        return $this->then($this->withOutputCallback($callback, $onlyIfOutputExists));
    }

    /**
     * Register a callback to be called if the operation succeeds.
	 * 注册一个回调函数，在操作成功时调用。
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function onSuccess(Closure $callback)
    {
        $parameters = $this->closureParameterTypes($callback);

        if (Arr::get($parameters, 'output') === Stringable::class) {
            return $this->onSuccessWithOutput($callback);
        }

        return $this->then(function (Container $container) use ($callback) {
            if ($this->exitCode === 0) {
                $container->call($callback);
            }
        });
    }

    /**
     * Register a callback that uses the output if the operation succeeds.
	 * 注册一个回调函数，在操作成功时使用输出。
     *
     * @param  \Closure  $callback
     * @param  bool  $onlyIfOutputExists
     * @return $this
     */
    public function onSuccessWithOutput(Closure $callback, $onlyIfOutputExists = false)
    {
        $this->ensureOutputIsBeingCaptured();

        return $this->onSuccess($this->withOutputCallback($callback, $onlyIfOutputExists));
    }

    /**
     * Register a callback to be called if the operation fails.
	 * 注册一个回调函数，以便在操作失败时调用。
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function onFailure(Closure $callback)
    {
        $parameters = $this->closureParameterTypes($callback);

        if (Arr::get($parameters, 'output') === Stringable::class) {
            return $this->onFailureWithOutput($callback);
        }

        return $this->then(function (Container $container) use ($callback) {
            if ($this->exitCode !== 0) {
                $container->call($callback);
            }
        });
    }

    /**
     * Register a callback that uses the output if the operation fails.
	 * 注册一个回调函数，在操作失败时使用输出。
     *
     * @param  \Closure  $callback
     * @param  bool  $onlyIfOutputExists
     * @return $this
     */
    public function onFailureWithOutput(Closure $callback, $onlyIfOutputExists = false)
    {
        $this->ensureOutputIsBeingCaptured();

        return $this->onFailure($this->withOutputCallback($callback, $onlyIfOutputExists));
    }

    /**
     * Get a callback that provides output.
	 * 获取一个提供输出的回调
     *
     * @param  \Closure  $callback
     * @param  bool  $onlyIfOutputExists
     * @return \Closure
     */
    protected function withOutputCallback(Closure $callback, $onlyIfOutputExists = false)
    {
        return function (Container $container) use ($callback, $onlyIfOutputExists) {
            $output = $this->output && is_file($this->output) ? file_get_contents($this->output) : '';

            return $onlyIfOutputExists && empty($output)
                            ? null
                            : $container->call($callback, ['output' => new Stringable($output)]);
        };
    }

    /**
     * Set the human-friendly description of the event.
	 * 设置事件的人性化描述
     *
     * @param  string  $description
     * @return $this
     */
    public function name($description)
    {
        return $this->description($description);
    }

    /**
     * Set the human-friendly description of the event.
	 * 设置事件的人性化描述
     *
     * @param  string  $description
     * @return $this
     */
    public function description($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the summary of the event for display.
	 * 获取要显示的事件摘要
     *
     * @return string
     */
    public function getSummaryForDisplay()
    {
        if (is_string($this->description)) {
            return $this->description;
        }

        return $this->buildCommand();
    }

    /**
     * Determine the next due date for an event.
	 * 确定事件的下一个截止日期
     *
     * @param  \DateTimeInterface|string  $currentTime
     * @param  int  $nth
     * @param  bool  $allowCurrentDate
     * @return \Illuminate\Support\Carbon
     */
    public function nextRunDate($currentTime = 'now', $nth = 0, $allowCurrentDate = false)
    {
        return Date::instance((new CronExpression($this->getExpression()))
            ->getNextRunDate($currentTime, $nth, $allowCurrentDate, $this->timezone));
    }

    /**
     * Get the Cron expression for the event.
	 * 获取事件的Cron表达式
     *
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * Set the event mutex implementation to be used.
	 * 设置要使用的事件互斥锁实现
     *
     * @param  \Illuminate\Console\Scheduling\EventMutex  $mutex
     * @return $this
     */
    public function preventOverlapsUsing(EventMutex $mutex)
    {
        $this->mutex = $mutex;

        return $this;
    }

    /**
     * Get the mutex name for the scheduled command.
	 * 获取计划命令的互斥对象名称
     *
     * @return string
     */
    public function mutexName()
    {
        $mutexNameResolver = $this->mutexNameResolver;

        if (! is_null($mutexNameResolver) && is_callable($mutexNameResolver)) {
            return $mutexNameResolver($this);
        }

        return 'framework'.DIRECTORY_SEPARATOR.'schedule-'.sha1($this->expression.$this->command);
    }

    /**
     * Set the mutex name or name resolver callback.
	 * 设置互斥对象名称或名称解析器回调
     *
     * @param  \Closure|string  $mutexName
     * @return $this
     */
    public function createMutexNameUsing(Closure|string $mutexName)
    {
        $this->mutexNameResolver = is_string($mutexName) ? fn () => $mutexName : $mutexName;

        return $this;
    }

    /**
     * Delete the mutex for the event.
	 * 删除事件的互斥锁
     *
     * @return void
     */
    protected function removeMutex()
    {
        if ($this->withoutOverlapping) {
            $this->mutex->forget($this);
        }
    }
}
