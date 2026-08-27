<?php
/**
 * Illuminate，邮件，邮件管理器
 */

namespace Illuminate\Mail;

use Aws\Ses\SesClient;
use Aws\SesV2\SesV2Client;
use Closure;
use Illuminate\Contracts\Mail\Factory as FactoryContract;
use Illuminate\Log\LogManager;
use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Mail\Transport\LogTransport;
use Illuminate\Mail\Transport\SesTransport;
use Illuminate\Mail\Transport\SesV2Transport;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mailer\Bridge\Mailgun\Transport\MailgunTransportFactory;
use Symfony\Component\Mailer\Bridge\Postmark\Transport\PostmarkTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\FailoverTransport;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;

/**
 * @mixin \Illuminate\Mail\Mailer
 */
class MailManager implements FactoryContract
{
    /**
     * The application instance.
	 * 应用实例
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The array of resolved mailers.
	 * 已解析邮件的数组
     *
     * @var array
     */
    protected $mailers = [];

    /**
     * The registered custom driver creators.
	 * 注册的自定义驱动程序创建者
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * Create a new Mail manager instance.
	 * 创建一个新的邮件管理器实例
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get a mailer instance by name.
	 * 按名称获取邮件实例
     *
     * @param  string|null  $name
     * @return \Illuminate\Contracts\Mail\Mailer
     */
    public function mailer($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->mailers[$name] = $this->get($name);
    }

    /**
     * Get a mailer driver instance.
	 * 获取邮件驱动程序实例
     *
     * @param  string|null  $driver
     * @return \Illuminate\Mail\Mailer
     */
    public function driver($driver = null)
    {
        return $this->mailer($driver);
    }

    /**
     * Attempt to get the mailer from the local cache.
	 * 尝试从本地缓存中获取邮件
     *
     * @param  string  $name
     * @return \Illuminate\Mail\Mailer
     */
    protected function get($name)
    {
        return $this->mailers[$name] ?? $this->resolve($name);
    }

    /**
     * Resolve the given mailer.
	 * 解析给定的邮件
     *
     * @param  string  $name
     * @return \Illuminate\Mail\Mailer
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Mailer [{$name}] is not defined.");
        }

        // Once we have created the mailer instance we will set a container instance
        // on the mailer. This allows us to resolve mailer classes via containers
        // for maximum testability on said classes instead of passing Closures.
		// 一旦我们创建了邮件实例，我们将在邮件上设置一个容器实例。
        $mailer = new Mailer(
            $name,
            $this->app['view'],
            $this->createSymfonyTransport($config),
            $this->app['events']
        );

        if ($this->app->bound('queue')) {
            $mailer->setQueue($this->app['queue']);
        }

        // Next we will set all of the global addresses on this mailer, which allows
        // for easy unification of all "from" addresses as well as easy debugging
        // of sent messages since these will be sent to a single email address.
		// 接下来，我们将设置此邮件上的所有全局地址。
        foreach (['from', 'reply_to', 'to', 'return_path'] as $type) {
            $this->setGlobalAddress($mailer, $config, $type);
        }

        return $mailer;
    }

    /**
     * Create a new transport instance.
	 * 创建一个新的传输实例
     *
     * @param  array  $config
     * @return \Symfony\Component\Mailer\Transport\TransportInterface
     *
     * @throws \InvalidArgumentException
     */
    public function createSymfonyTransport(array $config)
    {
        // Here we will check if the "transport" key exists and if it doesn't we will
        // assume an application is still using the legacy mail configuration file
        // format and use the "mail.driver" configuration option instead for BC.
		// 这里我们将检查"transport"键是否存在。
        $transport = $config['transport'] ?? $this->app['config']['mail.driver'];

        if (isset($this->customCreators[$transport])) {
            return call_user_func($this->customCreators[$transport], $config);
        }

        if (trim($transport ?? '') === '' ||
            ! method_exists($this, $method = 'create'.ucfirst(Str::camel($transport)).'Transport')) {
            throw new InvalidArgumentException("Unsupported mail transport [{$transport}].");
        }

        return $this->{$method}($config);
    }

    /**
     * Create an instance of the Symfony SMTP Transport driver.
	 * 建Symfony SMTP传输驱动程序的实例
     *
     * @param  array  $config
     * @return \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport
     */
    protected function createSmtpTransport(array $config)
    {
        $factory = new EsmtpTransportFactory;

        $scheme = $config['scheme'] ?? null;

        if (! $scheme) {
            $scheme = ! empty($config['encryption']) && $config['encryption'] === 'tls'
                ? (($config['port'] == 465) ? 'smtps' : 'smtp')
                : '';
        }

        $transport = $factory->create(new Dsn(
            $scheme,
            $config['host'],
            $config['username'] ?? null,
            $config['password'] ?? null,
            $config['port'] ?? null,
            $config
        ));

        return $this->configureSmtpTransport($transport, $config);
    }

    /**
     * Configure the additional SMTP driver options.
	 * 配置其他SMTP驱动程序选项
     *
     * @param  \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport  $transport
     * @param  array  $config
     * @return \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport
     */
    protected function configureSmtpTransport(EsmtpTransport $transport, array $config)
    {
        $stream = $transport->getStream();

        if ($stream instanceof SocketStream) {
            if (isset($config['source_ip'])) {
                $stream->setSourceIp($config['source_ip']);
            }

            if (isset($config['timeout'])) {
                $stream->setTimeout($config['timeout']);
            }
        }

        return $transport;
    }

    /**
     * Create an instance of the Symfony Sendmail Transport driver.
	 * 创建Symfony Sendmail Transport驱动程序的实例
     *
     * @param  array  $config
     * @return \Symfony\Component\Mailer\Transport\SendmailTransport
     */
    protected function createSendmailTransport(array $config)
    {
        return new SendmailTransport(
            $config['path'] ?? $this->app['config']->get('mail.sendmail')
        );
    }

    /**
     * Create an instance of the Symfony Amazon SES Transport driver.
	 * 创建Symfony Amazon SES Transport驱动程序的实例
     *
     * @param  array  $config
     * @return \Illuminate\Mail\Transport\SesTransport
     */
    protected function createSesTransport(array $config)
    {
        $config = array_merge(
            $this->app['config']->get('services.ses', []),
            ['version' => 'latest', 'service' => 'email'],
            $config
        );

        $config = Arr::except($config, ['transport']);

        return new SesTransport(
            new SesClient($this->addSesCredentials($config)),
            $config['options'] ?? []
        );
    }

    /**
     * Create an instance of the Symfony Amazon SES V2 Transport driver.
	 * 创建Symfony Amazon SES V2 Transport驱动程序的实例
     *
     * @param  array  $config
     * @return \Illuminate\Mail\Transport\Se2VwTransport
     */
    protected function createSesV2Transport(array $config)
    {
        $config = array_merge(
            $this->app['config']->get('services.ses', []),
            ['version' => 'latest'],
            $config
        );

        $config = Arr::except($config, ['transport']);

        return new SesV2Transport(
            new SesV2Client($this->addSesCredentials($config)),
            $config['options'] ?? []
        );
    }

    /**
     * Add the SES credentials to the configuration array.
	 * 将SES凭据添加到配置阵列
     *
     * @param  array  $config
     * @return array
     */
    protected function addSesCredentials(array $config)
    {
        if (! empty($config['key']) && ! empty($config['secret'])) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }

        return Arr::except($config, ['token']);
    }

    /**
     * Create an instance of the Symfony Mail Transport driver.
	 * 创建Symfony邮件传输驱动程序的实例
     *
     * @return \Symfony\Component\Mailer\Transport\SendmailTransport
     */
    protected function createMailTransport()
    {
        return new SendmailTransport;
    }

    /**
     * Create an instance of the Symfony Mailgun Transport driver.
	 * 创建Symfony Mailgun Transport驱动程序的实例
     *
     * @param  array  $config
     * @return \Symfony\Component\Mailer\Transport\TransportInterface
     */
    protected function createMailgunTransport(array $config)
    {
        $factory = new MailgunTransportFactory(null, $this->getHttpClient($config));

        if (! isset($config['secret'])) {
            $config = $this->app['config']->get('services.mailgun', []);
        }

        return $factory->create(new Dsn(
            'mailgun+'.($config['scheme'] ?? 'https'),
            $config['endpoint'] ?? 'default',
            $config['secret'],
            $config['domain']
        ));
    }

    /**
     * Create an instance of the Symfony Postmark Transport driver.
	 * 创建Symfony邮戳传输驱动程序的实例
     *
     * @param  array  $config
     * @return \Symfony\Component\Mailer\Bridge\Postmark\Transport\PostmarkApiTransport
     */
    protected function createPostmarkTransport(array $config)
    {
        $factory = new PostmarkTransportFactory(null, $this->getHttpClient($config));

        $options = isset($config['message_stream_id'])
                    ? ['message_stream' => $config['message_stream_id']]
                    : [];

        return $factory->create(new Dsn(
            'postmark+api',
            'default',
            $config['token'] ?? $this->app['config']->get('services.postmark.token'),
            null,
            null,
            $options
        ));
    }

    /**
     * Create an instance of the Symfony Failover Transport driver.
	 * 创建Symfony故障转移传输驱动程序的实例
     *
     * @param  array  $config
     * @return \Symfony\Component\Mailer\Transport\FailoverTransport
     */
    protected function createFailoverTransport(array $config)
    {
        $transports = [];

        foreach ($config['mailers'] as $name) {
            $config = $this->getConfig($name);

            if (is_null($config)) {
                throw new InvalidArgumentException("Mailer [{$name}] is not defined.");
            }

            // Now, we will check if the "driver" key exists and if it does we will set
            // the transport configuration parameter in order to offer compatibility
            // with any Laravel <= 6.x application style mail configuration files.
			// 现在，我们将检查“driver”键是否存在，如果存在，我们将进行设置传输配置参数。
            $transports[] = $this->app['config']['mail.driver']
                ? $this->createSymfonyTransport(array_merge($config, ['transport' => $name]))
                : $this->createSymfonyTransport($config);
        }

        return new FailoverTransport($transports);
    }

    /**
     * Create an instance of the Log Transport driver.
	 * 创建日志传输驱动程序的实例
     *
     * @param  array  $config
     * @return \Illuminate\Mail\Transport\LogTransport
     */
    protected function createLogTransport(array $config)
    {
        $logger = $this->app->make(LoggerInterface::class);

        if ($logger instanceof LogManager) {
            $logger = $logger->channel(
                $config['channel'] ?? $this->app['config']->get('mail.log_channel')
            );
        }

        return new LogTransport($logger);
    }

    /**
     * Create an instance of the Array Transport Driver.
	 * 创建阵列传输驱动程序的实例
     *
     * @return \Illuminate\Mail\Transport\ArrayTransport
     */
    protected function createArrayTransport()
    {
        return new ArrayTransport;
    }

    /**
     * Get a configured Symfony HTTP client instance.
	 * 获取已配置的Symfony HTTP客户端实例
     *
     * @return \Symfony\Contracts\HttpClient\HttpClientInterface|null
     */
    protected function getHttpClient(array $config)
    {
        if ($options = ($config['client'] ?? false)) {
            $maxHostConnections = Arr::pull($options, 'max_host_connections', 6);
            $maxPendingPushes = Arr::pull($options, 'max_pending_pushes', 50);

            return HttpClient::create($options, $maxHostConnections, $maxPendingPushes);
        }
    }

    /**
     * Set a global address on the mailer by type.
	 * 按类型在邮件上设置全局地址
     *
     * @param  \Illuminate\Mail\Mailer  $mailer
     * @param  array  $config
     * @param  string  $type
     * @return void
     */
    protected function setGlobalAddress($mailer, array $config, string $type)
    {
        $address = Arr::get($config, $type, $this->app['config']['mail.'.$type]);

        if (is_array($address) && isset($address['address'])) {
            $mailer->{'always'.Str::studly($type)}($address['address'], $address['name']);
        }
    }

    /**
     * Get the mail connection configuration.
	 * 获取邮件连接配置
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig(string $name)
    {
        // Here we will check if the "driver" key exists and if it does we will use
        // the entire mail configuration file as the "driver" config in order to
        // provide "BC" for any Laravel <= 6.x style mail configuration files.
		// 这里我们将检查"driver"键是否存在。
        return $this->app['config']['mail.driver']
            ? $this->app['config']['mail']
            : $this->app['config']["mail.mailers.{$name}"];
    }

    /**
     * Get the default mail driver name.
	 * 获取默认的邮件驱动程序名称
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        // Here we will check if the "driver" key exists and if it does we will use
        // that as the default driver in order to provide support for old styles
        // of the Laravel mail configuration file for backwards compatibility.
		// 这里我们将检查"driver"键是否存在，如果存在，我们将使用这是默认的驱动程序，以便提供对旧样式的支持。
        return $this->app['config']['mail.driver'] ??
            $this->app['config']['mail.default'];
    }

    /**
     * Set the default mail driver name.
	 * 设置默认的邮件驱动程序名称
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultDriver(string $name)
    {
        if ($this->app['config']['mail.driver']) {
            $this->app['config']['mail.driver'] = $name;
        }

        $this->app['config']['mail.default'] = $name;
    }

    /**
     * Disconnect the given mailer and remove from local cache.
	 * 断开给定邮件的连接并从本地缓存中删除
     *
     * @param  string|null  $name
     * @return void
     */
    public function purge($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        unset($this->mailers[$name]);
    }

    /**
     * Register a custom transport creator Closure.
	 * 注册自定义传输创建器闭包
     *
     * @param  string  $driver
     * @param  \Closure  $callback
     * @return $this
     */
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Get the application instance used by the manager.
	 * 获取管理器使用的应用程序实例
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    public function getApplication()
    {
        return $this->app;
    }

    /**
     * Set the application instance used by the manager.
	 * 设置管理员使用的应用实例
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return $this
     */
    public function setApplication($app)
    {
        $this->app = $app;

        return $this;
    }

    /**
     * Forget all of the resolved mailer instances.
	 * 忘记所有已解析的邮件实例
     *
     * @return $this
     */
    public function forgetMailers()
    {
        $this->mailers = [];

        return $this;
    }

    /**
     * Dynamically call the default driver instance.
	 * 动态调用默认驱动程序实例
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->mailer()->$method(...$parameters);
    }
}
