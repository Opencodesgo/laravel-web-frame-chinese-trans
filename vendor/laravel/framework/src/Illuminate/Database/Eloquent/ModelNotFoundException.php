<?php
/**
 * Illuminate，数据库，Eloquent，模型未找到异常
 */

namespace Illuminate\Database\Eloquent;

use Illuminate\Support\Arr;
use RuntimeException;

class ModelNotFoundException extends RuntimeException
{
    /**
     * Name of the affected Eloquent model.
	 * 受影响的Eloquent模型的名称
     *
     * @var string
     */
    protected $model;

    /**
     * The affected model IDs.
	 * 受影响的型号id
     *
     * @var int|array
     */
    protected $ids;

    /**
     * Set the affected Eloquent model and instance ids.
	 * 设置受影响的Eloquent模型和实例id
     *
     * @param  string  $model
     * @param  int|array  $ids
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
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get the affected Eloquent model IDs.
	 * 获取受影响的Eloquent模型id
     *
     * @return int|array
     */
    public function getIds()
    {
        return $this->ids;
    }
}
