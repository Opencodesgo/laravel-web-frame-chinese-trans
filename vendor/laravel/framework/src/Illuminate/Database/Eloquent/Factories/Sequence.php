<?php
/**
 * Illuminate，数据库，Eloquent，工厂，序列
 */

namespace Illuminate\Database\Eloquent\Factories;

use Countable;

class Sequence implements Countable
{
    /**
     * The sequence of return values.
	 * 返回值的序列
     *
     * @var array
     */
    protected $sequence;

    /**
     * The count of the sequence items.
	 * 序列项的计数
     *
     * @var int
     */
    public $count;

    /**
     * The current index of the sequence iteration.
	 * 序列迭代的当前索引
     *
     * @var int
     */
    public $index = 0;

    /**
     * Create a new sequence instance.
	 * 创建一个新的序列实例
     *
     * @param  mixed  ...$sequence
     * @return void
     */
    public function __construct(...$sequence)
    {
        $this->sequence = $sequence;
        $this->count = count($sequence);
    }

    /**
     * Get the current count of the sequence items.
	 * 获取序列项的当前计数
     *
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * Get the next value in the sequence.
	 * 获取序列中的下一个值
     *
     * @return mixed
     */
    public function __invoke()
    {
        return tap(value($this->sequence[$this->index % $this->count], $this), function () {
            $this->index = $this->index + 1;
        });
    }
}
