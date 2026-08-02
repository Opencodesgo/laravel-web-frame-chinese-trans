<?php
/**
 * NunoMaduro，冲突，供应者
 */

/**
 * This file is part of Collision.
 *
 * (c) Nuno Maduro <enunomaduro@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace NunoMaduro\Collision;

use NunoMaduro\Collision\Contracts\Handler as HandlerContract;
use NunoMaduro\Collision\Contracts\Provider as ProviderContract;
use Whoops\Run;
use Whoops\RunInterface;

/**
 * This is an Collision Provider implementation.
 * 这是一个冲突提供程序实现。
 *
 * @author Nuno Maduro <enunomaduro@gmail.com>
 */
class Provider implements ProviderContract
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
