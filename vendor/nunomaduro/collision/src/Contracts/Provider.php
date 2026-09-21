<?php
/**
 * NunoMaduro，Collision，契约，提供者
 */

declare(strict_types=1);

namespace NunoMaduro\Collision\Contracts;

/**
 * @internal
 */
interface Provider
{
    /**
     * Registers the current Handler as Error Handler.
	 * 将当前处理程序注册为错误处理程序
     *
     * @return \NunoMaduro\Collision\Contracts\Provider
     */
    public function register(): Provider;

    /**
     * Returns the handler.
	 * 返回处理程序
     *
     * @return \NunoMaduro\Collision\Contracts\Handler
     */
    public function getHandler(): Handler;
}
