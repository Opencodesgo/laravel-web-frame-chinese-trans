<?php
/**
 * Termwind，Laravel，终端服务提供商
 */

declare(strict_types=1);

namespace Termwind\Laravel;

use Illuminate\Console\OutputStyle;
use Illuminate\Support\ServiceProvider;
use Termwind\Termwind;

final class TermwindServiceProvider extends ServiceProvider
{
    /**
     * Sets the correct renderer to be used.
	 * 设置要使用的正确渲染器
     */
    public function register(): void
    {
        $this->app->resolving(OutputStyle::class, function ($style): void {
            Termwind::renderUsing($style->getOutput());
        });
    }
}
