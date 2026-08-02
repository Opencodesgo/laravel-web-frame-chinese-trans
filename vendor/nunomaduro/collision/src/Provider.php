<?php
/**
 * NunoMaduro，Collision，提供者
 */

declare(strict_types=1);

namespace NunoMaduro\Collision;

use NunoMaduro\Collision\Contracts\Handler as HandlerContract;
use NunoMaduro\Collision\Contracts\Provider as ProviderContract;
use Whoops\Run;
use Whoops\RunInterface;

/**
 * @internal
 *
 * @see \Tests\Unit\ProviderTest
 */
final class Provider implements ProviderContract
{
    /**
     * Holds an instance of the Run.
	 * 保存Run的实例
     *
     * @var \Whoops\RunInterface
     */
    protected $run;

    /**
     * Holds an instance of the handler.
	 * 保存处理程序的实例
     *
     * @var \NunoMaduro\Collision\Contracts\Handler
     */
    protected $handler;

    /**
     * Creates a new instance of the Provider.
	 * 创建提供程序的新实例
     */
    public function __construct(RunInterface $run = null, HandlerContract $handler = null)
    {
        $this->run     = $run ?: new Run();
        $this->handler = $handler ?: new Handler();
    }

    /**
     * {@inheritdoc}
     */
    public function register(): ProviderContract
    {
        $this->run->pushHandler($this->handler)
            ->register();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHandler(): HandlerContract
    {
        return $this->handler;
    }
}
