<?php
/**
 * Illuminate，数据库，Eloquent，工厂，交叉连接序列
 */

namespace Illuminate\Database\Eloquent\Factories;

use Illuminate\Support\Arr;

class CrossJoinSequence extends Sequence
{
    /**
     * Create a new cross join sequence instance.
	 * 创建一个新的交叉连接序列实例
     *
     * @param  array  ...$sequences
     * @return void
     */
    public function __construct(...$sequences)
    {
        $crossJoined = array_map(
            function ($a) {
                return array_merge(...$a);
            },
            Arr::crossJoin(...$sequences),
        );

        parent::__construct(...$crossJoined);
    }
}
