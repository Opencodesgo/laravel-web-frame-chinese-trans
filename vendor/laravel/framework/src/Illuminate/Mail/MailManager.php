<?php
/**
 * Illuminate，邮件，邮件管理
 */

namespace Illuminate\Mail;

use Aws\Ses\SesClient;
use Closure;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Contracts\Mail\Factory as FactoryContract;
use Illuminate\Log\LogManager;
use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Mail\Transport\LogTransport;
use Illuminate\Mail\Transport\MailgunTransport;
use Illuminate\Mail\Transport\SesTransport;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Postmark\ThrowExceptionOnFailurePlugin;
use Postmark\Transport as PostmarkTransport;
use Psr\Log\LoggerInterface;
use Swift_DependencyContainer;
use Swift_Mailer;
use Swift_SendmailTransport as SendmailTransport;
use Swift_SmtpTransport as SmtpTransport;

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
     * @return \Illuminate\Mail\Mailer
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
		// 一旦我们创建了邮件实例，我们将在邮件机中设置一个容器实例。
        $mailer = new Mailer(
            $name,
            $this->app['view'],
            $this->createSwiftMailer($config),
            $this->app['events']
        );

        if ($this->app->bound('queue')) {
            $mailer->setQueue($this->app['queue']);
        }

        // Next we will set all of the global addresses on this mailer, which allows
        // for easy unification of all "from" addresses as well as easy debugging
        // of sent messages since these will be sent to a single email address.
		// 接下来，我们将设置此邮件上的所有全局地址，这允许为了方便统一所有"from"地址。
        foreach (['from', 'reply_to', 'to', 'return_path'] as $type) {
            $this->setGlobalAddress($mailer, $config, $type);
        }

        return $mailer;
    }

    /**
     * Create the SwiftMailer instance for the given configuration.
	 * 为给定的配置创建SwiftMailer实例
     *
     * @param  array  $config
     * @return \Swift_Mailer
     */
    protected function createSwiftMailer(array $config)
    {
        if ($config['domain'] ?? false) {
            Swift_DependencyContainer::getInstance()
                ->register('mime.idgenerator.idright')
                ->asValue($config['domain']);
        }

        return new Swift_Mailer($this->createTransport($config));
    }

    /**
     * Create a new transport instance.
	 * 创建一个新的传输实例
     *
     * @param  array  $config
     * @return \Swift_Transport
     */
    public function createTransport(array $config)
    {
        // Here we will check if the "transport" key exists and if it doesn't we will
        // assume an application is still using the legacy mail configuration file
        // format and use the "mail.driver" configuration option instead for BC.
		// 这里我们将检查"transport"键是否存在，如果不存在，我们将检查假设应用程序仍在使用遗留邮件配置文件。
        $transport = $config['transport'] ?? $this->app['config']['mail.driver'];

        if (isset($this->customCreators[$transport])) {
            return call_user_func($this->customCreators[$transport], $config);
        }

        if (trim($transport) === '' || ! method_exists($this, $method = 'create'.ucfirst($transport).'Transport')) {
            throw new InvalidArgumentException("Unsupported mail transport [{$transport}].");
        }

        return $this->{$method}($config);
    }

    /**
     * Create an instance of the SMTP Swift Transport driver.
	 * 创建SMTP Swift传输驱动程序的实例
     *
     * @param  array  $config
     * @return \Swift_SmtpTransport
     */
    protected function createSmtpTransport(array $config)
    {
        // The Swift SMTP transport instance will allow us to use any SMTP backend
        // for delivering mail such as Sendgrid, Amazon SES, or a custom server
        // a developer has available. We will just pass this configured host.
		// Swift SMTP传输实例将允许我们使用任何SMTP后端用于发送邮件，如Sendgrid。
        $transport = new SmtpTransport(
            $config['host'],
            $config['port']
        );

        if (! empty($config['encryption'])) {
            $transport->setEncryption($config['encryption']);
        }

        // Once we have the transport we will check for the presence of a username
        // and password. If we have it we will set the credentials on the Swift
        // transporter instance so that we'll properly authenticate delivery.
		// 一旦有了传输，我们将检查用户名和密码是否存在。
        if (isset($config['username'])) {
            $transport->setUsername($config['username']);

            $transport->setPassword($config['password']);
        }

        return $this->configureSmtpTransport($transport, $config);
    }

    /**
     * Configure the additional SMTP driver options.
	 * 配置其他SMTP驱动程序选项
     *
     * @param  \Swift_SmtpTransport  $transport
     * @param  array  $config
     * @return \Swift_SmtpTransport
     */
    protected function configureSmtpTransport($transport, array $config)
    {
        if (isset($config['stream'])) {
            $transport->setStreamOptions($config['stream']);
        }

        if (isset($config['source_ip'])) {
            $transport->setSourceIp($config['source_ip']);
        }

        if (isset($config['local_domain'])) {
            $transport->setLocalDomain($config['local_domain']);
        }

        if (isset($config['timeout'])) {
            $transport->setTimeout($config['timeout']);
        }

        if (isset($config['auth_mode'])) {
            $transport->setAuthMode($config['auth_mode']);
        }

        return $transport;
    }

    /**
     * Create an instance of the Sendmail Swift Transport driver.
	 * 创建Sendmail Swift Transport驱动程序的实例
     *
     * @param  array  $config
     * @return \Swift_SendmailTransport
     */
    protected function createSendmailTransport(array $config)
    {
        return new SendmailTransport(
            $config['path'] ?? $this->app['config']->get('mail.sendmail')
        );
    }

    /**
     * Create an instance of the Amazon SES Swift Transport driver.
	 * 创建一个Amazon SES Swift Transport驱动程序的实例
     *
     * @param  array  $config
     * @return \Illuminate\Mail\Transport\SesTransport
     */
    protected function createSesTransport(array $config)
    {
        if (! isset($config['secret'])) {
            $config = array_merge($this->app['config']->get('services.ses', []), [
                'version' => 'latest', 'service' => 'email',
            ]);
        }

        $config = Arr::except($config, ['transport']);

        return new SesTransport(
            new SesClient($this->addSesCredentials($config)),
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

        return $config;
    }

    /**
     * Create an instance of the Mail Swift Transport driver.
	 * 创建邮件Swift传输驱动程序的实例
     *
     * @return \Swift_SendmailTransport
     */
    protected function createMailTransport()
    {
        return new SendmailTransport;
    }

    /**
     * Create an instance of the Mailgun Swift Transport driver.
	 * 创建Mailgun Swift Transport驱动程序的实例
     *
     * @param  array  $config
     * @return \Illuminate\Mail\Transport\MailgunTransport
     */
    protected function createMailgunTransport(array $config)
    {
        if (! isset($config['secret'])) {
            $config = $this->app['config']->get('services.mailgun', []);
        }

        return new MailgunTransport(
            $this->guzzle($config),
            $config['secret'],
            $config['domain'],
            $config['endpoint'] ?? null
        );
    }

    /**
     * Create an instance of the Postmark Swift Transport driver.
	 * 创建邮戳Swift传输驱动程序的实例
     *
     * @param  array  $config
     * @return \Swift_Transport
     */
    protected function createPostmarkTransport(array $config)
    {
        return tap(new PostmarkTransport(
            $config['token'] ?? $this->app['config']->get('services.postmark.token')
        ), function ($transport) {
            $transport->registerPlugin(new ThrowExceptionOnFailurePlugin());
        });
    }

    /**
     * Create an instance of the Log Swift Transport driver.
	 * 创建Log Swift Transport驱动程序的实例
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
     * Create an instance of the Array Swift Transport Driver.
	 * 创建Array Swift Transport Driver的实例
     *
     * @return \Illuminate\Mail\Transport\ArrayTransport
     */
    protected function createArrayTransport()
    {
        return new ArrayTransport;
    }

    /**
     * Get a fresh Guzzle HTTP client instance.
	 * 获取一个新的Guzzle HTTP客户端实例
     *
     * @param  array  $config
     * @return \GuzzleHttp\Client
     */
    protected function guzzle(array $config)
    {
        return new HttpClient(Arr::add(
            $config['guzzle'] ?? [],
            'connect_timeout',
            60
        ));
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
		// 这里我们将检查"driver"键是否存在，如果存在，我们将使用将整个邮件配置文件作为"驱动程序"配置。
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
		// 这里我们将检查"driver"键是否存在，如果存在，我们将使用默认的驱动程序，以便提供对旧样式的支持。
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
