<?php
/**
 * Illuminate，契约，翻译，有地区偏好
 */

namespace Illuminate\Contracts\Translation;

interface HasLocalePreference
{
    /**
     * Get the preferred locale of the entity.
	 * 获取实体的首选语言环境
     *
     * @return string|null
     */
    public function preferredLocale();
}
