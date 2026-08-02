<?php
/**
 * Illuminate，视图，引擎，PHP 引擎
 */

namespace Illuminate\View\Engines;

use Illuminate\Contracts\View\Engine;
use Throwable;

class PhpEngine implements Engine
{
    /**
     * Get the evaluated contents of the view.
	 * 获取视图的评估内容
     *
     * @param  string  $path
     * @param  array  $data
     * @return string
     */
    public function get($path, array $data = [])
    {
        return $this->evaluatePath($path, $data);
    }

    /**
     * Get the evaluated contents of the view at the given path.
	 * 获取给定路径上的视图的求值内容
     *
     * @param  string  $__path
     * @param  array  $__data
     * @return string
     */
    protected function evaluatePath($__path, $__data)
    {
        $obLevel = ob_get_level();

        ob_start();

        extract($__data, EXTR_SKIP);

        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
		// 我们将在try/catch块中计算视图的内容，以便我们可以清除任何杂散输出。
        try {
            include $__path;
        } catch (Throwable $e) {
            $this->handleViewException($e, $obLevel);
        }

        return ltrim(ob_get_clean());
    }

    /**
     * Handle a view exception.
	 * 处理视图异常
     *
     * @param  \Throwable  $e
     * @param  int  $obLevel
     * @return void
     *
     * @throws \Throwable
     */
    protected function handleViewException(Throwable $e, $obLevel)
    {
        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }

        throw $e;
    }
}
