<?php
/**
 * League，Flysystem，配置感知特性
 */

namespace League\Flysystem;

/**
 * @internal
 */
trait ConfigAwareTrait
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * Set the config.
	 * 设置配置
     *
     * @param Config|array|null $config
     */
    protected function setConfig($config)
    {
        $this->config = $config ? Util::ensureConfig($config) : new Config;
    }

    /**
     * Get the Config.
	 * 得到配置
     *
     * @return Config config object
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Convert a config array to a Config object with the correct fallback.
	 * 将配置数组转换为具有正确回退的配置对象
     *
     * @param array $config
     *
     * @return Config
     */
    protected function prepareConfig(array $config)
    {
        $config = new Config($config);
        $config->setFallback($this->getConfig());

        return $config;
    }
}
