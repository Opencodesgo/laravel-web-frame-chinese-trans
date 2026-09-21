<?php
/**
 * Illuminate，基础，测试，使用伪造者
 */

namespace Illuminate\Foundation\Testing;

use Faker\Factory;
use Faker\Generator;

trait WithFaker
{
    /**
     * The Faker instance.
	 * Faker的实例
     *
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * Setup up the Faker instance.
	 * 设置Faker实例
     *
     * @return void
     */
    protected function setUpFaker()
    {
        $this->faker = $this->makeFaker();
    }

    /**
     * Get the default Faker instance for a given locale.
	 * 获取给定语言环境的默认Faker实例
     *
     * @param  string|null  $locale
     * @return \Faker\Generator
     */
    protected function faker($locale = null)
    {
        return is_null($locale) ? $this->faker : $this->makeFaker($locale);
    }

    /**
     * Create a Faker instance for the given locale.
	 * 为给定的语言环境创建一个Faker实例
     *
     * @param  string|null  $locale
     * @return \Faker\Generator
     */
    protected function makeFaker($locale = null)
    {
        if (isset($this->app)) {
            $locale ??= $this->app->make('config')->get('app.faker_locale', Factory::DEFAULT_LOCALE);

            if ($this->app->bound(Generator::class)) {
                return $this->app->make(Generator::class, ['locale' => $locale]);
            }
        }

        return Factory::create($locale ?? Factory::DEFAULT_LOCALE);
    }
}
