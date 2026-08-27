<?php
/**
 * Illuminate，契约，调试，异常处理程序
 */

namespace Illuminate\Contracts\Debug;

use Throwable;

interface ExceptionHandler
{
    /**
     * Report or log an exception.
	 * 报告或记录异常
     *
     * @param  \Throwable  $e
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $e);

    /**
     * Determine if the exception should be reported.
	 * 确定是否应该报告异常
     *
     * @param  \Throwable  $e
     * @return bool
     */
    public function shouldReport(Throwable $e);

    /**
     * Render an exception into an HTTP response.
	 * 将异常呈现到HTTP响应中
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e);

    /**
     * Render an exception to the console.
	 * 向控制台呈现一个异常
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @param  \Throwable  $e
     * @return void
     *
     * @internal This method is not meant to be used or overwritten outside the framework.
     */
    public function renderForConsole($output, Throwable $e);
}
