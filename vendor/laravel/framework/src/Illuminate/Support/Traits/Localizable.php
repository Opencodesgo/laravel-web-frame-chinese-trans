<?php
/**
 * Illuminate，支持，特征，可本地化
 */

namespace Illuminate\Support\Traits;

use Illuminate\Container\Container;

trait Localizable
{
    /**
     * Run the callback with the given locale.
	 * 使用给定的区域运行回调
     *
     * @param  string  $locale
     * @param  \Closure  $callback
     * @return mixed
     */
    public function withLocale($locale, $callback)
    {
        if (! $locale) {
            return $callback();
        }

        $app = Container::getInstance();

        $original = $app->getLocale();

        try {
            $app->setLocale($locale);

            return $callback();
        } finally {
            $app->setLocale($original);
        }
    }
}
