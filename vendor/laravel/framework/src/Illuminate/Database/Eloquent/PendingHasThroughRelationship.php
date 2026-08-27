<?php
/**
 * Illuminate，数据库，Eloquent，待定已通过关系
 */

namespace Illuminate\Database\Eloquent;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PendingHasThroughRelationship
{
    /**
     * The root model that the relationship exists on.
	 * 关系存在的根模型
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $rootModel;

    /**
     * The local relationship.
	 * 本地关系
     *
     * @var \Illuminate\Database\Eloquent\Relations\HasMany|\Illuminate\Database\Eloquent\Relations\HasOne
     */
    protected $localRelationship;

    /**
     * Create a pending has-many-through or has-one-through relationship.
	 * 创建挂起的"多次通过"或"一次通过"关系
     *
     * @param  \Illuminate\Database\Eloquent\Model  $rootModel
     * @param  \Illuminate\Database\Eloquent\Relations\HasMany|\Illuminate\Database\Eloquent\Relations\HasOne  $localRelationship
     */
    public function __construct($rootModel, $localRelationship)
    {
        $this->rootModel = $rootModel;

        $this->localRelationship = $localRelationship;
    }

    /**
     * Define the distant relationship that this model has.
	 * 定义这个模型的远端关系
     *
     * @param  string|(callable(\Illuminate\Database\Eloquent\Model): (\Illuminate\Database\Eloquent\Relations\HasOne|\Illuminate\Database\Eloquent\Relations\HasMany))  $callback
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough|\Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function has($callback)
    {
        if (is_string($callback)) {
            $callback = fn () => $this->localRelationship->getRelated()->{$callback}();
        }

        $distantRelation = $callback($this->localRelationship->getRelated());

        if ($distantRelation instanceof HasMany) {
            return $this->rootModel->hasManyThrough(
                $distantRelation->getRelated()::class,
                $this->localRelationship->getRelated()::class,
                $this->localRelationship->getForeignKeyName(),
                $distantRelation->getForeignKeyName(),
                $this->localRelationship->getLocalKeyName(),
                $distantRelation->getLocalKeyName(),
            );
        }

        return $this->rootModel->hasOneThrough(
            $distantRelation->getRelated()::class,
            $this->localRelationship->getRelated()::class,
            $this->localRelationship->getForeignKeyName(),
            $distantRelation->getForeignKeyName(),
            $this->localRelationship->getLocalKeyName(),
            $distantRelation->getLocalKeyName(),
        );
    }

    /**
     * Handle dynamic method calls into the model.
	 * 处理对模型的动态方法调用
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (Str::startsWith($method, 'has')) {
            return $this->has(Str::of($method)->after('has')->lcfirst()->toString());
        }

        throw new BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()', static::class, $method
        ));
    }
}
