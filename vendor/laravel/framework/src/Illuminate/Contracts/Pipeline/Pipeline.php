<?php
/**
 * Illuminate，契约，管道，管道
 */

namespace Illuminate\Contracts\Pipeline;

use Closure;

interface Pipeline
{
    /**
     * Set the traveler object being sent on the pipeline.
	 * 设置在管道上发送的旅行者对象
     *
     * @param  mixed  $traveler
     * @return $this
     */
    public function send($traveler);

    /**
     * Set the stops of the pipeline.
	 * 设置管道的止水带
     *
     * @param  dynamic|array  $stops
     * @return $this
     */
    public function through($stops);

    /**
     * Set the method to call on the stops.
	 * 将该方法设置为在止损时调用
     *
     * @param  string  $method
     * @return $this
     */
    public function via($method);

    /**
     * Run the pipeline with a final destination callback.
	 * 运行带有最终目的地回调的管道
     *
     * @param  \Closure  $destination
     * @return mixed
     */
    public function then(Closure $destination);
}
