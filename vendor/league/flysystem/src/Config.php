<?php
/**
 * League，Flysystem，配置
 */

namespace League\Flysystem;

class Config
{
    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var Config|null
     */
    protected $fallback;

    /**
     * Constructor.
	 * 构造函数
     *
     * @param array $settings
     */
    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    /**
     * Get a setting.
	 * 得到设置
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed config setting or default when not found
     */
    public function get($key, $default = null)
    {
        if ( ! array_key_exists($key, $this->settings)) {
            return $this->getDefault($key, $default);
        }

        return $this->settings[$key];
    }

    /**
     * Check if an item exists by key.
	 * 检查项是否按键存在
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        if (array_key_exists($key, $this->settings)) {
            return true;
        }

        return $this->fallback instanceof Config
            ? $this->fallback->has($key)
            : false;
    }

    /**
     * Try to retrieve a default setting from a config fallback.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed config setting or default when not found
     */
    protected function getDefault($key, $default)
    {
        if ( ! $this->fallback) {
            return $default;
        }

        return $this->fallback->get($key, $default);
    }

    /**
     * Set a setting.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        $this->settings[$key] = $value;

        return $this;
    }

    /**
     * Set the fallback.
	 * 设置回调 
     *
     * @param Config $fallback
     *
     * @return $this
     */
    public function setFallback(Config $fallback)
    {
        $this->fallback = $fallback;

        return $this;
    }
}
