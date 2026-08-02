<?php
/**
 * Illuminate，数据库，Eloquent，Json 编码异常
 */

namespace Illuminate\Database\Eloquent;

use RuntimeException;

class JsonEncodingException extends RuntimeException
{
    /**
     * Create a new JSON encoding exception for the model.
	 * 为模型创建一个新的JSON编码异常
     *
     * @param  mixed  $model
     * @param  string  $message
     * @return static
     */
    public static function forModel($model, $message)
    {
        return new static('Error encoding model ['.get_class($model).'] with ID ['.$model->getKey().'] to JSON: '.$message);
    }

    /**
     * Create a new JSON encoding exception for the resource.
	 * 为资源创建一个新的JSON编码异常
     *
     * @param  \Illuminate\Http\Resources\Json\JsonResource  $resource
     * @param  string  $message
     * @return static
     */
    public static function forResource($resource, $message)
    {
        $model = $resource->resource;

        return new static('Error encoding resource ['.get_class($resource).'] with model ['.get_class($model).'] with ID ['.$model->getKey().'] to JSON: '.$message);
    }

    /**
     * Create a new JSON encoding exception for an attribute.
	 * 为属性创建一个新的JSON编码异常
     *
     * @param  mixed  $model
     * @param  mixed  $key
     * @param  string  $message
     * @return static
     */
    public static function forAttribute($model, $key, $message)
    {
        $class = get_class($model);

        return new static("Unable to encode attribute [{$key}] for model [{$class}] to JSON: {$message}.");
    }
}
