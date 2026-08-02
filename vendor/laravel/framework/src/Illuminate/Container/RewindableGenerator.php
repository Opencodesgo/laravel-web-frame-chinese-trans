<?php
/**
 * Illuminate，容器，可倒带生成器
 */

namespace Illuminate\Container;

use Countable;
use IteratorAggregate;

class RewindableGenerator implements Countable, IteratorAggregate
{
    /**
     * The generator callback.
	 * 生成器回调
     *
     * @var callable
     */
    protected $generator;

    /**
     * The number of tagged services.
	 * 标记服务的数量
     *
     * @var callable|int
     */
    protected $count;

    /**
     * Create a new generator instance.
	 * 创建新的生成器实例
     *
     * @param  callable  $generator
     * @param  callable|int  $count
     * @return void
     */
    public function __construct(callable $generator, $count)
    {
        $this->count = $count;
        $this->generator = $generator;
    }

    /**
     * Get an iterator from the generator.
	 * 从生成器获取迭代器
     *
     * @return mixed
     */
    public function getIterator()
    {
        return ($this->generator)();
    }

    /**
     * Get the total number of tagged services.
	 * 获取标记服务的总数
     *
     * @return int
     */
    public function count()
    {
        if (is_callable($count = $this->count)) {
            $this->count = $count();
        }

        return $this->count;
    }
}
