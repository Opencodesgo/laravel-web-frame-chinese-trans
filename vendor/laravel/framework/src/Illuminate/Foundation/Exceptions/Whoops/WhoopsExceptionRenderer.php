<?php
/**
 * Illuminate，基础，异常，Whoops异常渲染器
 */

namespace Illuminate\Foundation\Exceptions\Whoops;

use Illuminate\Contracts\Foundation\ExceptionRenderer;
use Whoops\Run as Whoops;

use function tap;

class WhoopsExceptionRenderer implements ExceptionRenderer
{
    /**
     * Renders the given exception as HTML.
	 * 将给定的异常呈现为HTML
     *
     * @param  \Throwable  $throwable
     * @return string
     */
    public function render($throwable)
    {
        return tap(new Whoops, function ($whoops) {
            $whoops->appendHandler($this->whoopsHandler());

            $whoops->writeToOutput(false);

            $whoops->allowQuit(false);
        })->handleException($throwable);
    }

    /**
     * Get the Whoops handler for the application.
	 * 获取应用程序的Whoops处理程序
     *
     * @return \Whoops\Handler\Handler
     */
    protected function whoopsHandler()
    {
        return (new WhoopsHandler)->forDebug();
    }
}
