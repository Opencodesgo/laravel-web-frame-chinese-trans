<?php
/**
 * Illuminate，认证，调用器
 */

namespace Illuminate\Auth;

class Recaller
{
    /**
     * The "recaller" / "remember me" cookie string.
	 * "回忆者"/"记住我"cookie字符串
     *
     * @var string
     */
    protected $recaller;

    /**
     * Create a new recaller instance.
	 * 创建新的调用程序实例
     *
     * @param  string  $recaller
     * @return void
     */
    public function __construct($recaller)
    {
        $this->recaller = @unserialize($recaller, ['allowed_classes' => false]) ?: $recaller;
    }

    /**
     * Get the user ID from the recaller.
	 * 从调用器中获取用户ID
     *
     * @return string
     */
    public function id()
    {
        return explode('|', $this->recaller, 3)[0];
    }

    /**
     * Get the "remember token" token from the recaller.
	 * 从调用器获得"记住令牌"令牌
     *
     * @return string
     */
    public function token()
    {
        return explode('|', $this->recaller, 3)[1];
    }

    /**
     * Get the password from the recaller.
	 * 从召回器中获取密码
     *
     * @return string
     */
    public function hash()
    {
        return explode('|', $this->recaller, 4)[2];
    }

    /**
     * Determine if the recaller is valid.
	 * 确定调用器是否有效
     *
     * @return bool
     */
    public function valid()
    {
        return $this->properString() && $this->hasAllSegments();
    }

    /**
     * Determine if the recaller is an invalid string.
	 * 确定调用器是否是无效字符串
     *
     * @return bool
     */
    protected function properString()
    {
        return is_string($this->recaller) && str_contains($this->recaller, '|');
    }

    /**
     * Determine if the recaller has all segments.
	 * 确定召回器是否具有所有段
     *
     * @return bool
     */
    protected function hasAllSegments()
    {
        $segments = explode('|', $this->recaller);

        return count($segments) >= 3 && trim($segments[0]) !== '' && trim($segments[1]) !== '';
    }

    /**
     * Get the recaller's segments.
	 * 得到召回者的片段
     *
     * @return array
     */
    public function segments()
    {
        return explode('|', $this->recaller);
    }
}
