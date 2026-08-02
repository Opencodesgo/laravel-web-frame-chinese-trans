<?php
/**
 * Illuminate，测试，流畅的，问题，排除故障
 */

namespace Illuminate\Testing\Fluent\Concerns;

trait Debugging
{
    /**
     * Dumps the given props.
	 * 转储给定的道具
     *
     * @param  string|null  $prop
     * @return $this
     */
    public function dump(string $prop = null): self
    {
        dump($this->prop($prop));

        return $this;
    }

    /**
     * Dumps the given props and exits.
	 * 转储给定的道具并退出
     *
     * @param  string|null  $prop
     * @return void
     */
    public function dd(string $prop = null): void
    {
        dd($this->prop($prop));
    }

    /**
     * Retrieve a prop within the current scope using "dot" notation.
	 * 使用"点"表示法检索当前作用域中的道具
     *
     * @param  string|null  $key
     * @return mixed
     */
    abstract protected function prop(string $key = null);
}
