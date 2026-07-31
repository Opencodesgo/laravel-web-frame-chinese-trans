<?php
/**
 * NunoMaduro，Collision，契约，适配器，Php单元，监听者
 */

declare(strict_types=1);

namespace NunoMaduro\Collision\Contracts\Adapters\Phpunit;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListener;

/**
 * @internal
 */
interface Listener extends TestListener
{
    /**
     * Renders the provided error
     * on the console.
	 * 呈现所提供的错误在控制台上
     *
     * @return void
     */
    public function render(Test $test, \Throwable $t);
}
