<?php
/**
 * Illuminate，认证，访问，事件，大门评估
 */

namespace Illuminate\Auth\Access\Events;

class GateEvaluated
{
    /**
     * The authenticatable model.
	 * 可验证的模型
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public $user;

    /**
     * The ability being evaluated.
	 * 被评估的能力
     *
     * @var string
     */
    public $ability;

    /**
     * The result of the evaluation.
	 * 评估的结果
     *
     * @var bool|null
     */
    public $result;

    /**
     * The arguments given during evaluation.
	 * 评估期间给出的参数
     *
     * @var array
     */
    public $arguments;

    /**
     * Create a new event instance.
	 * 创建新的事件实例
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|null  $user
     * @param  string  $ability
     * @param  bool|null  $result
     * @param  array  $arguments
     * @return void
     */
    public function __construct($user, $ability, $result, $arguments)
    {
        $this->user = $user;
        $this->ability = $ability;
        $this->result = $result;
        $this->arguments = $arguments;
    }
}
