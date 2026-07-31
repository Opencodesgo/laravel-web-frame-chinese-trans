<?php declare(strict_types=1);

/**
 * Monolog，处理程序，New Relic 处理程序
 *

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Handler;

use Monolog\Logger;
use Monolog\Utils;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Formatter\FormatterInterface;

/**
 * Class to record a log on a NewRelic application.
 * Enabling New Relic High Security mode may prevent capture of useful information.
 * 在一个新应用程序上记录一个日志。
 *
 * This handler requires a NormalizerFormatter to function and expects an array in $record['formatted']
 *
 * @see https://docs.newrelic.com/docs/agents/php-agent
 * @see https://docs.newrelic.com/docs/accounts-partnerships/accounts/security/high-security
 */
class NewRelicHandler extends AbstractProcessingHandler
{
    /**
     * Name of the New Relic application that will receive logs from this handler.
	 * 将从这个处理程序接收日志的新遗物应用程序的名称。
     *
     * @var ?string
     */
    protected $appName;

    /**
     * Name of the current transaction
	 * 当前事务的名称
     *
     * @var ?string
     */
    protected $transactionName;

    /**
     * Some context and extra data is passed into the handler as arrays of values. Do we send them as is
     * (useful if we are using the API), or explode them for display on the NewRelic RPM website?
	 * 一些上下文和额外的数据被传递到处理程序中,作为值的数组。
     *
     * @var bool
     */
    protected $explodeArrays;

    /**
     * {@inheritDoc}
     *
     * @param string|null $appName
     * @param bool        $explodeArrays
     * @param string|null $transactionName
     */
    public function __construct(
        $level = Logger::ERROR,
        bool $bubble = true,
        ?string $appName = null,
        bool $explodeArrays = false,
        ?string $transactionName = null
    ) {
        parent::__construct($level, $bubble);

        $this->appName       = $appName;
        $this->explodeArrays = $explodeArrays;
        $this->transactionName = $transactionName;
    }

    /**
     * {@inheritDoc}
     */
    protected function write(array $record): void
    {
        if (!$this->isNewRelicEnabled()) {
            throw new MissingExtensionException('The newrelic PHP extension is required to use the NewRelicHandler');
        }

        if ($appName = $this->getAppName($record['context'])) {
            $this->setNewRelicAppName($appName);
        }

        if ($transactionName = $this->getTransactionName($record['context'])) {
            $this->setNewRelicTransactionName($transactionName);
            unset($record['formatted']['context']['transaction_name']);
        }

        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof \Throwable) {
            newrelic_notice_error($record['message'], $record['context']['exception']);
            unset($record['formatted']['context']['exception']);
        } else {
            newrelic_notice_error($record['message']);
        }

        if (isset($record['formatted']['context']) && is_array($record['formatted']['context'])) {
            foreach ($record['formatted']['context'] as $key => $parameter) {
                if (is_array($parameter) && $this->explodeArrays) {
                    foreach ($parameter as $paramKey => $paramValue) {
                        $this->setNewRelicParameter('context_' . $key . '_' . $paramKey, $paramValue);
                    }
                } else {
                    $this->setNewRelicParameter('context_' . $key, $parameter);
                }
            }
        }

        if (isset($record['formatted']['extra']) && is_array($record['formatted']['extra'])) {
            foreach ($record['formatted']['extra'] as $key => $parameter) {
                if (is_array($parameter) && $this->explodeArrays) {
                    foreach ($parameter as $paramKey => $paramValue) {
                        $this->setNewRelicParameter('extra_' . $key . '_' . $paramKey, $paramValue);
                    }
                } else {
                    $this->setNewRelicParameter('extra_' . $key, $parameter);
                }
            }
        }
    }

    /**
     * Checks whether the NewRelic extension is enabled in the system.
	 * 检查系统中是否启用了新残端扩展
     *
     * @return bool
     */
    protected function isNewRelicEnabled(): bool
    {
        return extension_loaded('newrelic');
    }

    /**
     * Returns the appname where this log should be sent. Each log can override the default appname, set in this
     * handler's constructor, by providing the appname in it's context.
	 * 返回该日志应该发送的appname。
     *
     * @param mixed[] $context
     */
    protected function getAppName(array $context): ?string
    {
        if (isset($context['appname'])) {
            return $context['appname'];
        }

        return $this->appName;
    }

    /**
     * Returns the name of the current transaction. Each log can override the default transaction name, set in this
     * handler's constructor, by providing the transaction_name in it's context
	 * 返回当前事务的名称。每个日志都可以覆盖默认的事务名称，设置在此处理程序的构造函数，通过在其上下文中提供transaction_name。
     *
     * @param mixed[] $context
     */
    protected function getTransactionName(array $context): ?string
    {
        if (isset($context['transaction_name'])) {
            return $context['transaction_name'];
        }

        return $this->transactionName;
    }

    /**
     * Sets the NewRelic application that should receive this log.
	 * 设置应该接收此日志的新应用程序
     */
    protected function setNewRelicAppName(string $appName): void
    {
        newrelic_set_appname($appName);
    }

    /**
     * Overwrites the name of the current transaction
	 * 重写当前事务的名称
     */
    protected function setNewRelicTransactionName(string $transactionName): void
    {
        newrelic_name_transaction($transactionName);
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    protected function setNewRelicParameter(string $key, $value): void
    {
        if (null === $value || is_scalar($value)) {
            newrelic_add_custom_parameter($key, $value);
        } else {
            newrelic_add_custom_parameter($key, Utils::jsonEncode($value, null, true));
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new NormalizerFormatter();
    }
}
