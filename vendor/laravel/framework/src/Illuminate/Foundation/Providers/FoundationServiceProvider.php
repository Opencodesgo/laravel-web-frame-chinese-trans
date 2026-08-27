<?php
/**
 * Illuminate，基础，提供者，基础服务提供商
 */

namespace Illuminate\Foundation\Providers;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\MaintenanceMode as MaintenanceModeContract;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Grammar;
use Illuminate\Foundation\Console\CliDumper;
use Illuminate\Foundation\Http\HtmlDumper;
use Illuminate\Foundation\MaintenanceModeManager;
use Illuminate\Foundation\Precognition;
use Illuminate\Foundation\Vite;
use Illuminate\Http\Request;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\AggregateServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\LoggedExceptionCollection;
use Illuminate\Testing\ParallelTestingServiceProvider;
use Illuminate\Validation\ValidationException;
use Symfony\Component\VarDumper\Caster\StubCaster;
use Symfony\Component\VarDumper\Cloner\AbstractCloner;

class FoundationServiceProvider extends AggregateServiceProvider
{
    /**
     * The provider class names.
	 * 提供程序类名
     *
     * @var string[]
     */
    protected $providers = [
        FormRequestServiceProvider::class,
        ParallelTestingServiceProvider::class,
    ];

    /**
     * The singletons to register into the container.
	 * 要注册到容器中的单例
     *
     * @var array
     */
    public $singletons = [
        Vite::class => Vite::class,
    ];

    /**
     * Boot the service provider.
	 * 启动服务提供程序
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../Exceptions/views' => $this->app->resourcePath('views/errors/'),
            ], 'laravel-errors');
        }
    }

    /**
     * Register the service provider.
	 * 注册服务提供者
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->registerDumper();
        $this->registerRequestValidation();
        $this->registerRequestSignatureValidation();
        $this->registerExceptionTracking();
        $this->registerMaintenanceModeManager();
    }

    /**
     * Register an var dumper (with source) to debug variables.
	 * 注册一个变量转储器(带源代码)来调试变量
     *
     * @return void
     */
    public function registerDumper()
    {
        AbstractCloner::$defaultCasters[ConnectionInterface::class] = [StubCaster::class, 'cutInternals'];
        AbstractCloner::$defaultCasters[Container::class] = [StubCaster::class, 'cutInternals'];
        AbstractCloner::$defaultCasters[Dispatcher::class] = [StubCaster::class, 'cutInternals'];
        AbstractCloner::$defaultCasters[Factory::class] = [StubCaster::class, 'cutInternals'];
        AbstractCloner::$defaultCasters[Grammar::class] = [StubCaster::class, 'cutInternals'];

        $basePath = $this->app->basePath();

        $compiledViewPath = $this->app['config']->get('view.compiled');

        $format = $_SERVER['VAR_DUMPER_FORMAT'] ?? null;

        match (true) {
            'html' == $format => HtmlDumper::register($basePath, $compiledViewPath),
            'cli' == $format => CliDumper::register($basePath, $compiledViewPath),
            'server' == $format => null,
            $format && 'tcp' == parse_url($format, PHP_URL_SCHEME) => null,
            default => in_array(PHP_SAPI, ['cli', 'phpdbg']) ? CliDumper::register($basePath, $compiledViewPath) : HtmlDumper::register($basePath, $compiledViewPath),
        };
    }

    /**
     * Register the "validate" macro on the request.
	 * 在请求上注册"validate"宏
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function registerRequestValidation()
    {
        Request::macro('validate', function (array $rules, ...$params) {
            return tap(validator($this->all(), $rules, ...$params), function ($validator) {
                if ($this->isPrecognitive()) {
                    $validator->after(Precognition::afterValidationHook($this))
                        ->setRules(
                            $this->filterPrecognitiveRules($validator->getRulesWithoutPlaceholders())
                        );
                }
            })->validate();
        });

        Request::macro('validateWithBag', function (string $errorBag, array $rules, ...$params) {
            try {
                return $this->validate($rules, ...$params);
            } catch (ValidationException $e) {
                $e->errorBag = $errorBag;

                throw $e;
            }
        });
    }

    /**
     * Register the "hasValidSignature" macro on the request.
	 * 在请求上注册"hasValidSignature"宏
     *
     * @return void
     */
    public function registerRequestSignatureValidation()
    {
        Request::macro('hasValidSignature', function ($absolute = true) {
            return URL::hasValidSignature($this, $absolute);
        });

        Request::macro('hasValidRelativeSignature', function () {
            return URL::hasValidSignature($this, $absolute = false);
        });

        Request::macro('hasValidSignatureWhileIgnoring', function ($ignoreQuery = [], $absolute = true) {
            return URL::hasValidSignature($this, $absolute, $ignoreQuery);
        });
    }

    /**
     * Register an event listener to track logged exceptions.
	 * 注册一个事件侦听器来跟踪记录的异常
     *
     * @return void
     */
    protected function registerExceptionTracking()
    {
        if (! $this->app->runningUnitTests()) {
            return;
        }

        $this->app->instance(
            LoggedExceptionCollection::class,
            new LoggedExceptionCollection
        );

        $this->app->make('events')->listen(MessageLogged::class, function ($event) {
            if (isset($event->context['exception'])) {
                $this->app->make(LoggedExceptionCollection::class)
                        ->push($event->context['exception']);
            }
        });
    }

    /**
     * Register the maintenance mode manager service.
	 * 注册维护模式管理器服务
     *
     * @return void
     */
    public function registerMaintenanceModeManager()
    {
        $this->app->singleton(MaintenanceModeManager::class);

        $this->app->bind(
            MaintenanceModeContract::class,
            fn () => $this->app->make(MaintenanceModeManager::class)->driver()
        );
    }
}
