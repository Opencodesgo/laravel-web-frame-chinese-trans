<?php
/**
 * Illuminate，数据库，Eloquent，模型未发现异常
 */

namespace Illuminate\Database\Eloquent;

use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Arr;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
class ModelNotFoundException extends RecordsNotFoundException
{
    /**
     * Name of the affected Eloquent model.
	 * 受影响的Eloquent模型的名称
     *
     * @var class-string<TModel>
     */
    protected $model;

    /**
     * The affected model IDs.
	 * 受影响的模型IDS
     *
     * @var array<int, int|string>
     */
    protected $ids;

    /**
     * Set the affected Eloquent model and instance ids.
	 * 设置受影响的Eloquent模型和实例id
     *
     * @param  class-string<TModel>  $model
     * @param  array<int, int|string>|int|string  $ids
     * @return $this
     */
    public function setModel($model, $ids = [])
    {
        $this->model = $model;
        $this->ids = Arr::wrap($ids);

        $this->message = "No query results for model [{$model}]";

        if (count($this->ids) > 0) {
            $this->message .= ' '.implode(', ', $this->ids);
        } else {
            $this->message .= '.';
        }

        return $this;
    }

    /**
     * Get the affected Eloquent model.
	 * 获取受影响的Eloquent模型
     *
     * @return class-string<TModel>
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get the affected Eloquent model IDs.
	 * 获取受影响的Eloquent模型
     *
     * @return array<int, int|string>
     */
    public function getIds()
    {
        return $this->ids;
    }
}
