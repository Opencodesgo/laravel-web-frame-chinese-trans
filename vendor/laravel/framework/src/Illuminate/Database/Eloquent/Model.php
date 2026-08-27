<?php
/**
 * Illuminate，数据库，Eloquent，模型抽象类
 */

namespace Illuminate\Database\Eloquent;

use ArrayAccess;
use Illuminate\Contracts\Broadcasting\HasBroadcastChannel;
use Illuminate\Contracts\Queue\QueueableCollection;
use Illuminate\Contracts\Queue\QueueableEntity;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\CanBeEscapedWhenCastToString;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\ConnectionResolverInterface as Resolver;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use JsonSerializable;
use LogicException;

abstract class Model implements Arrayable, ArrayAccess, CanBeEscapedWhenCastToString, HasBroadcastChannel, Jsonable, JsonSerializable, QueueableEntity, UrlRoutable
{
    use Concerns\HasAttributes,
        Concerns\HasEvents,
        Concerns\HasGlobalScopes,
        Concerns\HasRelationships,
        Concerns\HasTimestamps,
        Concerns\HidesAttributes,
        Concerns\GuardsAttributes,
        ForwardsCalls;

    /**
     * The connection name for the model.
	 * 模型的连接名称
     *
     * @var string|null
     */
    protected $connection;

    /**
     * The table associated with the model.
	 * 与模型相关联的表
     *
     * @var string
     */
    protected $table;

    /**
     * The primary key for the model.
	 * 模型的主键
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The "type" of the primary key ID.
	 * 主键ID的"类型"
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Indicates if the IDs are auto-incrementing.
	 * 指示IDs是否自动递增
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The relations to eager load on every query.
	 * 到eager的关系在每个查询上加载
     *
     * @var array
     */
    protected $with = [];

    /**
     * The relationship counts that should be eager loaded on every query.
	 * 应该在每个查询上立即加载的关系计数
     *
     * @var array
     */
    protected $withCount = [];

    /**
     * Indicates whether lazy loading will be prevented on this model.
	 * 指示是否在此模型上阻止延迟加载
     *
     * @var bool
     */
    public $preventsLazyLoading = false;

    /**
     * The number of models to return for pagination.
	 * 要为分页返回的模型数
     *
     * @var int
     */
    protected $perPage = 15;

    /**
     * Indicates if the model exists.
	 * 指示模型是否存在
     *
     * @var bool
     */
    public $exists = false;

    /**
     * Indicates if the model was inserted during the current request lifecycle.
	 * 指示模型是否在当前请求生命周期中插入
     *
     * @var bool
     */
    public $wasRecentlyCreated = false;

    /**
     * Indicates that the object's string representation should be escaped when __toString is invoked.
	 * 指示在调用__toString时应该转义对象的字符串表示形式
     *
     * @var bool
     */
    protected $escapeWhenCastingToString = false;

    /**
     * The connection resolver instance.
	 * 连接解析器实例
     *
     * @var \Illuminate\Database\ConnectionResolverInterface
     */
    protected static $resolver;

    /**
     * The event dispatcher instance.
	 * 事件调度程序实例
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected static $dispatcher;

    /**
     * The array of booted models.
	 * 启动模型的数组
     *
     * @var array
     */
    protected static $booted = [];

    /**
     * The array of trait initializers that will be called on each new instance.
	 * 将在每个新实例上调用的trait初始化器数组
     *
     * @var array
     */
    protected static $traitInitializers = [];

    /**
     * The array of global scopes on the model.
	 * 模型上的全局作用域数组
     *
     * @var array
     */
    protected static $globalScopes = [];

    /**
     * The list of models classes that should not be affected with touch.
	 * 不受touch影响的模型类列表
     *
     * @var array
     */
    protected static $ignoreOnTouch = [];

    /**
     * Indicates whether lazy loading should be restricted on all models.
	 * 指示是否应在所有模型上限制延迟加载
     *
     * @var bool
     */
    protected static $modelsShouldPreventLazyLoading = false;

    /**
     * The callback that is responsible for handling lazy loading violations.
	 * 负责处理延迟加载违规的回调
     *
     * @var callable|null
     */
    protected static $lazyLoadingViolationCallback;

    /**
     * Indicates if an exception should be thrown instead of silently discarding non-fillable attributes.
	 * 指示是否应抛出异常，而不是静默地丢弃不可填充的属性。
     *
     * @var bool
     */
    protected static $modelsShouldPreventSilentlyDiscardingAttributes = false;

    /**
     * The callback that is responsible for handling discarded attribute violations.
	 * 负责处理丢弃的属性违规的回调
     *
     * @var callable|null
     */
    protected static $discardedAttributeViolationCallback;

    /**
     * Indicates if an exception should be thrown when trying to access a missing attribute on a retrieved model.
	 * 指示在尝试访问检索模型上的缺失属性时是否应抛出异常
     *
     * @var bool
     */
    protected static $modelsShouldPreventAccessingMissingAttributes = false;

    /**
     * The callback that is responsible for handling missing attribute violations.
	 * 负责处理缺失的属性违规的回调
     *
     * @var callable|null
     */
    protected static $missingAttributeViolationCallback;

    /**
     * Indicates if broadcasting is currently enabled.
	 * 指示当前是否启用广播
     *
     * @var bool
     */
    protected static $isBroadcasting = true;

    /**
     * The name of the "created at" column.
	 * "created at"列的名称
     *
     * @var string|null
     */
    const CREATED_AT = 'created_at';

    /**
     * The name of the "updated at" column.
	 * "updated at"列的名称
     *
     * @var string|null
     */
    const UPDATED_AT = 'updated_at';

    /**
     * Create a new Eloquent model instance.
	 * 创建新的Eloquent模型实例
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->bootIfNotBooted();

        $this->initializeTraits();

        $this->syncOriginal();

        $this->fill($attributes);
    }

    /**
     * Check if the model needs to be booted and if so, do it.
	 * 检查模型是否需要启动，如果需要，就启动。
     *
     * @return void
     */
    protected function bootIfNotBooted()
    {
        if (! isset(static::$booted[static::class])) {
            static::$booted[static::class] = true;

            $this->fireModelEvent('booting', false);

            static::booting();
            static::boot();
            static::booted();

            $this->fireModelEvent('booted', false);
        }
    }

    /**
     * Perform any actions required before the model boots.
	 * 在模型启动之前执行所需的任何操作
     *
     * @return void
     */
    protected static function booting()
    {
        //
    }

    /**
     * Bootstrap the model and its traits.
	 * 引导模型及其特征
     *
     * @return void
     */
    protected static function boot()
    {
        static::bootTraits();
    }

    /**
     * Boot all of the bootable traits on the model.
	 * 启动模型上所有可启动的特征
     *
     * @return void
     */
    protected static function bootTraits()
    {
        $class = static::class;

        $booted = [];

        static::$traitInitializers[$class] = [];

        foreach (class_uses_recursive($class) as $trait) {
            $method = 'boot'.class_basename($trait);

            if (method_exists($class, $method) && ! in_array($method, $booted)) {
                forward_static_call([$class, $method]);

                $booted[] = $method;
            }

            if (method_exists($class, $method = 'initialize'.class_basename($trait))) {
                static::$traitInitializers[$class][] = $method;

                static::$traitInitializers[$class] = array_unique(
                    static::$traitInitializers[$class]
                );
            }
        }
    }

    /**
     * Initialize any initializable traits on the model.
	 * 初始化模型上任何可初始化的特征
     *
     * @return void
     */
    protected function initializeTraits()
    {
        foreach (static::$traitInitializers[static::class] as $method) {
            $this->{$method}();
        }
    }

    /**
     * Perform any actions required after the model boots.
	 * 在模型启动后执行所需的任何操作
     *
     * @return void
     */
    protected static function booted()
    {
        //
    }

    /**
     * Clear the list of booted models so they will be re-booted.
	 * 清除已启动的模型列表，以便它们将被重新启动。
     *
     * @return void
     */
    public static function clearBootedModels()
    {
        static::$booted = [];

        static::$globalScopes = [];
    }

    /**
     * Disables relationship model touching for the current class during given callback scope.
	 * 在给定的回调范围内禁用当前类的关系模型触摸
     *
     * @param  callable  $callback
     * @return void
     */
    public static function withoutTouching(callable $callback)
    {
        static::withoutTouchingOn([static::class], $callback);
    }

    /**
     * Disables relationship model touching for the given model classes during given callback scope.
	 * 在给定的回调范围内，为给定的模型类禁用关系模型触摸。
     *
     * @param  array  $models
     * @param  callable  $callback
     * @return void
     */
    public static function withoutTouchingOn(array $models, callable $callback)
    {
        static::$ignoreOnTouch = array_values(array_merge(static::$ignoreOnTouch, $models));

        try {
            $callback();
        } finally {
            static::$ignoreOnTouch = array_values(array_diff(static::$ignoreOnTouch, $models));
        }
    }

    /**
     * Determine if the given model is ignoring touches.
	 * 确定给定模型是否忽略触摸
     *
     * @param  string|null  $class
     * @return bool
     */
    public static function isIgnoringTouch($class = null)
    {
        $class = $class ?: static::class;

        if (! get_class_vars($class)['timestamps'] || ! $class::UPDATED_AT) {
            return true;
        }

        foreach (static::$ignoreOnTouch as $ignoredClass) {
            if ($class === $ignoredClass || is_subclass_of($class, $ignoredClass)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Indicate that models should prevent lazy loading, silently discarding attributes, and accessing missing attributes.
     *
     * @param  bool  $shouldBeStrict
     * @return void
     */
    public static function shouldBeStrict(bool $shouldBeStrict = true)
    {
        static::preventLazyLoading($shouldBeStrict);
        static::preventSilentlyDiscardingAttributes($shouldBeStrict);
        static::preventAccessingMissingAttributes($shouldBeStrict);
    }

    /**
     * Prevent model relationships from being lazy loaded.
	 * 防止模型关系被惰性加载
     *
     * @param  bool  $value
     * @return void
     */
    public static function preventLazyLoading($value = true)
    {
        static::$modelsShouldPreventLazyLoading = $value;
    }

    /**
     * Register a callback that is responsible for handling lazy loading violations.
	 * 注册一个负责处理延迟加载违规的回调
     *
     * @param  callable|null  $callback
     * @return void
     */
    public static function handleLazyLoadingViolationUsing(?callable $callback)
    {
        static::$lazyLoadingViolationCallback = $callback;
    }

    /**
     * Prevent non-fillable attributes from being silently discarded.
	 * 防止不可填充属性被静默丢弃
     *
     * @param  bool  $value
     * @return void
     */
    public static function preventSilentlyDiscardingAttributes($value = true)
    {
        static::$modelsShouldPreventSilentlyDiscardingAttributes = $value;
    }

    /**
     * Register a callback that is responsible for handling discarded attribute violations.
	 * 注册一个负责处理丢弃的属性违规的回调
     *
     * @param  callable|null  $callback
     * @return void
     */
    public static function handleDiscardedAttributeViolationUsing(?callable $callback)
    {
        static::$discardedAttributeViolationCallback = $callback;
    }

    /**
     * Prevent accessing missing attributes on retrieved models.
	 * 防止访问检索模型上缺失的属性
     *
     * @param  bool  $value
     * @return void
     */
    public static function preventAccessingMissingAttributes($value = true)
    {
        static::$modelsShouldPreventAccessingMissingAttributes = $value;
    }

    /**
     * Register a callback that is responsible for handling lazy loading violations.
	 * 注册一个负责处理延迟加载违规的回调
     *
     * @param  callable|null  $callback
     * @return void
     */
    public static function handleMissingAttributeViolationUsing(?callable $callback)
    {
        static::$missingAttributeViolationCallback = $callback;
    }

    /**
     * Execute a callback without broadcasting any model events for all model types.
	 * 执行回调，而不为所有模型类型广播任何模型事件。
     *
     * @param  callable  $callback
     * @return mixed
     */
    public static function withoutBroadcasting(callable $callback)
    {
        $isBroadcasting = static::$isBroadcasting;

        static::$isBroadcasting = false;

        try {
            return $callback();
        } finally {
            static::$isBroadcasting = $isBroadcasting;
        }
    }

    /**
     * Fill the model with an array of attributes.
	 * 用属性数组填充模型
     *
     * @param  array  $attributes
     * @return $this
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function fill(array $attributes)
    {
        $totallyGuarded = $this->totallyGuarded();

        $fillable = $this->fillableFromArray($attributes);

        foreach ($fillable as $key => $value) {
            // The developers may choose to place some attributes in the "fillable" array
            // which means only those attributes may be set through mass assignment to
            // the model, and all others will just get ignored for security reasons.
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            } elseif ($totallyGuarded || static::preventsSilentlyDiscardingAttributes()) {
                if (isset(static::$discardedAttributeViolationCallback)) {
                    call_user_func(static::$discardedAttributeViolationCallback, $this, [$key]);
                } else {
                    throw new MassAssignmentException(sprintf(
                        'Add [%s] to fillable property to allow mass assignment on [%s].',
                        $key, get_class($this)
                    ));
                }
            }
        }

        if (count($attributes) !== count($fillable) &&
            static::preventsSilentlyDiscardingAttributes()) {
            $keys = array_diff(array_keys($attributes), array_keys($fillable));

            if (isset(static::$discardedAttributeViolationCallback)) {
                call_user_func(static::$discardedAttributeViolationCallback, $this, $keys);
            } else {
                throw new MassAssignmentException(sprintf(
                    'Add fillable property [%s] to allow mass assignment on [%s].',
                    implode(', ', $keys),
                    get_class($this)
                ));
            }
        }

        return $this;
    }

    /**
     * Fill the model with an array of attributes. Force mass assignment.
	 * 用属性数组填充模型。力质量分配。
     *
     * @param  array  $attributes
     * @return $this
     */
    public function forceFill(array $attributes)
    {
        return static::unguarded(fn () => $this->fill($attributes));
    }

    /**
     * Qualify the given column name by the model's table.
	 * 根据模型的表限定给定的列名
     *
     * @param  string  $column
     * @return string
     */
    public function qualifyColumn($column)
    {
        if (str_contains($column, '.')) {
            return $column;
        }

        return $this->getTable().'.'.$column;
    }

    /**
     * Qualify the given columns with the model's table.
	 * 用模型的表限定给定的列
     *
     * @param  array  $columns
     * @return array
     */
    public function qualifyColumns($columns)
    {
        return collect($columns)->map(function ($column) {
            return $this->qualifyColumn($column);
        })->all();
    }

    /**
     * Create a new instance of the given model.
	 * 创建给定模型的新实例
     *
     * @param  array  $attributes
     * @param  bool  $exists
     * @return static
     */
    public function newInstance($attributes = [], $exists = false)
    {
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.
        $model = new static;

        $model->exists = $exists;

        $model->setConnection(
            $this->getConnectionName()
        );

        $model->setTable($this->getTable());

        $model->mergeCasts($this->casts);

        $model->fill((array) $attributes);

        return $model;
    }

    /**
     * Create a new model instance that is existing.
	 * 创建一个现有的新模型实例
     *
     * @param  array  $attributes
     * @param  string|null  $connection
     * @return static
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $model = $this->newInstance([], true);

        $model->setRawAttributes((array) $attributes, true);

        $model->setConnection($connection ?: $this->getConnectionName());

        $model->fireModelEvent('retrieved', false);

        return $model;
    }

    /**
     * Begin querying the model on a given connection.
	 * 开始查询给定连接上的模型
     *
     * @param  string|null  $connection
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function on($connection = null)
    {
        // First we will just create a fresh instance of this model, and then we can set the
        // connection on the model so that it is used for the queries we execute, as well
        // as being set on every relation we retrieve without a custom connection name.
		// 首先，我们将创建这个模型的一个新实例，
        $instance = new static;

        $instance->setConnection($connection);

        return $instance->newQuery();
    }

    /**
     * Begin querying the model on the write connection.
	 * 开始查询写连接上的模型
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function onWriteConnection()
    {
        return static::query()->useWritePdo();
    }

    /**
     * Get all of the models from the database.
	 * 从数据库中获取所有的模型
     *
     * @param  array|string  $columns
     * @return \Illuminate\Database\Eloquent\Collection<int, static>
     */
    public static function all($columns = ['*'])
    {
        return static::query()->get(
            is_array($columns) ? $columns : func_get_args()
        );
    }

    /**
     * Begin querying a model with eager loading.
	 * 开始查询具有即时加载的模型
     *
     * @param  array|string  $relations
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function with($relations)
    {
        return static::query()->with(
            is_string($relations) ? func_get_args() : $relations
        );
    }

    /**
     * Eager load relations on the model.
	 * 模型上的急切载荷关系
     *
     * @param  array|string  $relations
     * @return $this
     */
    public function load($relations)
    {
        $query = $this->newQueryWithoutRelationships()->with(
            is_string($relations) ? func_get_args() : $relations
        );

        $query->eagerLoadRelations([$this]);

        return $this;
    }

    /**
     * Eager load relationships on the polymorphic relation of a model.
	 * 模型的多态关系上的动态加载关系
     *
     * @param  string  $relation
     * @param  array  $relations
     * @return $this
     */
    public function loadMorph($relation, $relations)
    {
        if (! $this->{$relation}) {
            return $this;
        }

        $className = get_class($this->{$relation});

        $this->{$relation}->load($relations[$className] ?? []);

        return $this;
    }

    /**
     * Eager load relations on the model if they are not already eager loaded.
	 * 模型上的急切加载关系，如果它们还没有急切加载的话。
     *
     * @param  array|string  $relations
     * @return $this
     */
    public function loadMissing($relations)
    {
        $relations = is_string($relations) ? func_get_args() : $relations;

        $this->newCollection([$this])->loadMissing($relations);

        return $this;
    }

    /**
     * Eager load relation's column aggregations on the model.
	 * 急于荷载关系的列聚集在模型上
     *
     * @param  array|string  $relations
     * @param  string  $column
     * @param  string  $function
     * @return $this
     */
    public function loadAggregate($relations, $column, $function = null)
    {
        $this->newCollection([$this])->loadAggregate($relations, $column, $function);

        return $this;
    }

    /**
     * Eager load relation counts on the model.
     *
     * @param  array|string  $relations
     * @return $this
     */
    public function loadCount($relations)
    {
        $relations = is_string($relations) ? func_get_args() : $relations;

        return $this->loadAggregate($relations, '*', 'count');
    }

    /**
     * Eager load relation max column values on the model.
     *
     * @param  array|string  $relations
     * @param  string  $column
     * @return $this
     */
    public function loadMax($relations, $column)
    {
        return $this->loadAggregate($relations, $column, 'max');
    }

    /**
     * Eager load relation min column values on the model.
     *
     * @param  array|string  $relations
     * @param  string  $column
     * @return $this
     */
    public function loadMin($relations, $column)
    {
        return $this->loadAggregate($relations, $column, 'min');
    }

    /**
     * Eager load relation's column summations on the model.
     *
     * @param  array|string  $relations
     * @param  string  $column
     * @return $this
     */
    public function loadSum($relations, $column)
    {
        return $this->loadAggregate($relations, $column, 'sum');
    }

    /**
     * Eager load relation average column values on the model.
     *
     * @param  array|string  $relations
     * @param  string  $column
     * @return $this
     */
    public function loadAvg($relations, $column)
    {
        return $this->loadAggregate($relations, $column, 'avg');
    }

    /**
     * Eager load related model existence values on the model.
	 * 热切载荷相关的模型存在值在模型上
     *
     * @param  array|string  $relations
     * @return $this
     */
    public function loadExists($relations)
    {
        return $this->loadAggregate($relations, '*', 'exists');
    }

    /**
     * Eager load relationship column aggregation on the polymorphic relation of a model.
	 * 基于多态关系的动态加载关系列聚合模型
     *
     * @param  string  $relation
     * @param  array  $relations
     * @param  string  $column
     * @param  string  $function
     * @return $this
     */
    public function loadMorphAggregate($relation, $relations, $column, $function = null)
    {
        if (! $this->{$relation}) {
            return $this;
        }

        $className = get_class($this->{$relation});

        $this->{$relation}->loadAggregate($relations[$className] ?? [], $column, $function);

        return $this;
    }

    /**
     * Eager load relationship counts on the polymorphic relation of a model.
	 * 动态负载关系依赖于模型的多态关系
     *
     * @param  string  $relation
     * @param  array  $relations
     * @return $this
     */
    public function loadMorphCount($relation, $relations)
    {
        return $this->loadMorphAggregate($relation, $relations, '*', 'count');
    }

    /**
     * Eager load relationship max column values on the polymorphic relation of a model.
	 * 动态加载关系模型多态关系上的最大列值
     *
     * @param  string  $relation
     * @param  array  $relations
     * @param  string  $column
     * @return $this
     */
    public function loadMorphMax($relation, $relations, $column)
    {
        return $this->loadMorphAggregate($relation, $relations, $column, 'max');
    }

    /**
     * Eager load relationship min column values on the polymorphic relation of a model.
	 * 模型的多态关系上的最小列值
     *
     * @param  string  $relation
     * @param  array  $relations
     * @param  string  $column
     * @return $this
     */
    public function loadMorphMin($relation, $relations, $column)
    {
        return $this->loadMorphAggregate($relation, $relations, $column, 'min');
    }

    /**
     * Eager load relationship column summations on the polymorphic relation of a model.
	 * 急于加载关系列对模型多态关系的求和
     *
     * @param  string  $relation
     * @param  array  $relations
     * @param  string  $column
     * @return $this
     */
    public function loadMorphSum($relation, $relations, $column)
    {
        return $this->loadMorphAggregate($relation, $relations, $column, 'sum');
    }

    /**
     * Eager load relationship average column values on the polymorphic relation of a model.
	 * 急于加载关系是模型多态关系上列值的平均
     *
     * @param  string  $relation
     * @param  array  $relations
     * @param  string  $column
     * @return $this
     */
    public function loadMorphAvg($relation, $relations, $column)
    {
        return $this->loadMorphAggregate($relation, $relations, $column, 'avg');
    }

    /**
     * Increment a column's value by a given amount.
	 * 将列的值增加给定的量
     *
     * @param  string  $column
     * @param  float|int  $amount
     * @param  array  $extra
     * @return int
     */
    protected function increment($column, $amount = 1, array $extra = [])
    {
        return $this->incrementOrDecrement($column, $amount, $extra, 'increment');
    }

    /**
     * Decrement a column's value by a given amount.
	 * 将列的值递减给定的量
     *
     * @param  string  $column
     * @param  float|int  $amount
     * @param  array  $extra
     * @return int
     */
    protected function decrement($column, $amount = 1, array $extra = [])
    {
        return $this->incrementOrDecrement($column, $amount, $extra, 'decrement');
    }

    /**
     * Run the increment or decrement method on the model.
	 * 在模型上运行增量或递减方法
     *
     * @param  string  $column
     * @param  float|int  $amount
     * @param  array  $extra
     * @param  string  $method
     * @return int
     */
    protected function incrementOrDecrement($column, $amount, $extra, $method)
    {
        $query = $this->newQueryWithoutRelationships();

        if (! $this->exists) {
            return $query->{$method}($column, $amount, $extra);
        }

        $this->{$column} = $this->isClassDeviable($column)
            ? $this->deviateClassCastableAttribute($method, $column, $amount)
            : $this->{$column} + ($method === 'increment' ? $amount : $amount * -1);

        $this->forceFill($extra);

        if ($this->fireModelEvent('updating') === false) {
            return false;
        }

        return tap($this->setKeysForSaveQuery($query)->{$method}($column, $amount, $extra), function () use ($column) {
            $this->syncChanges();

            $this->fireModelEvent('updated', false);

            $this->syncOriginalAttribute($column);
        });
    }

    /**
     * Update the model in the database.
	 * 更新数据库中的模型
     *
     * @param  array  $attributes
     * @param  array  $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = [])
    {
        if (! $this->exists) {
            return false;
        }

        return $this->fill($attributes)->save($options);
    }

    /**
     * Update the model in the database within a transaction.
	 * 在事务中更新数据库中的模型
     *
     * @param  array  $attributes
     * @param  array  $options
     * @return bool
     *
     * @throws \Throwable
     */
    public function updateOrFail(array $attributes = [], array $options = [])
    {
        if (! $this->exists) {
            return false;
        }

        return $this->fill($attributes)->saveOrFail($options);
    }

    /**
     * Update the model in the database without raising any events.
	 * 在不引发任何事件的情况下更新数据库中的模型
     *
     * @param  array  $attributes
     * @param  array  $options
     * @return bool
     */
    public function updateQuietly(array $attributes = [], array $options = [])
    {
        if (! $this->exists) {
            return false;
        }

        return $this->fill($attributes)->saveQuietly($options);
    }

    /**
     * Increment a column's value by a given amount without raising any events.
	 * 在不引发任何事件的情况下，将列的值增加给定的量。
     *
     * @param  string  $column
     * @param  float|int  $amount
     * @param  array  $extra
     * @return int
     */
    protected function incrementQuietly($column, $amount = 1, array $extra = [])
    {
        return static::withoutEvents(function () use ($column, $amount, $extra) {
            return $this->incrementOrDecrement($column, $amount, $extra, 'increment');
        });
    }

    /**
     * Decrement a column's value by a given amount without raising any events.
	 * 在不引发任何事件的情况下，将列的值递减给定的量。
     *
     * @param  string  $column
     * @param  float|int  $amount
     * @param  array  $extra
     * @return int
     */
    protected function decrementQuietly($column, $amount = 1, array $extra = [])
    {
        return static::withoutEvents(function () use ($column, $amount, $extra) {
            return $this->incrementOrDecrement($column, $amount, $extra, 'decrement');
        });
    }

    /**
     * Save the model and all of its relationships.
	 * 保存模型及其所有关系
     *
     * @return bool
     */
    public function push()
    {
        if (! $this->save()) {
            return false;
        }

        // To sync all of the relationships to the database, we will simply spin through
        // the relationships and save each model via this "push" method, which allows
        // us to recurse into all of these nested relations for the model instance.
		// 要将所有关系同步到数据库，我们将简单地旋转过去。
        foreach ($this->relations as $models) {
            $models = $models instanceof Collection
                ? $models->all() : [$models];

            foreach (array_filter($models) as $model) {
                if (! $model->push()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Save the model and all of its relationships without raising any events to the parent model.
	 * 保存模型及其所有关系，而不向父模型引发任何事件。
     *
     * @return bool
     */
    public function pushQuietly()
    {
        return static::withoutEvents(fn () => $this->push());
    }

    /**
     * Save the model to the database without raising any events.
	 * 在不引发任何事件的情况下将模型保存到数据库中
     *
     * @param  array  $options
     * @return bool
     */
    public function saveQuietly(array $options = [])
    {
        return static::withoutEvents(fn () => $this->save($options));
    }

    /**
     * Save the model to the database.
	 * 将模型保存到数据库中
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = [])
    {
        $this->mergeAttributesFromCachedCasts();

        $query = $this->newModelQuery();

        // If the "saving" event returns false we'll bail out of the save and return
        // false, indicating that the save failed. This provides a chance for any
        // listeners to cancel save operations if validations fail or whatever.
        if ($this->fireModelEvent('saving') === false) {
            return false;
        }

        // If the model already exists in the database we can just update our record
        // that is already in this database using the current IDs in this "where"
        // clause to only update this model. Otherwise, we'll just insert them.
		// 如果模型已经存在于数据库中，我们可以更新我们的记录。
        if ($this->exists) {
            $saved = $this->isDirty() ?
                $this->performUpdate($query) : true;
        }

        // If the model is brand new, we'll insert it into our database and set the
        // ID attribute on the model to the value of the newly inserted row's ID
        // which is typically an auto-increment value managed by the database.
        else {
            $saved = $this->performInsert($query);

            if (! $this->getConnectionName() &&
                $connection = $query->getConnection()) {
                $this->setConnection($connection->getName());
            }
        }

        // If the model is successfully saved, we need to do a few more things once
        // that is done. We will call the "saved" method here to run any actions
        // we need to happen after a model gets successfully saved right here.
        if ($saved) {
            $this->finishSave($options);
        }

        return $saved;
    }

    /**
     * Save the model to the database within a transaction.
	 * 将模型保存到事务中的数据库中
     *
     * @param  array  $options
     * @return bool
     *
     * @throws \Throwable
     */
    public function saveOrFail(array $options = [])
    {
        return $this->getConnection()->transaction(fn () => $this->save($options));
    }

    /**
     * Perform any actions that are necessary after the model is saved.
	 * 在保存模型后执行任何必要的操作
     *
     * @param  array  $options
     * @return void
     */
    protected function finishSave(array $options)
    {
        $this->fireModelEvent('saved', false);

        if ($this->isDirty() && ($options['touch'] ?? true)) {
            $this->touchOwners();
        }

        $this->syncOriginal();
    }

    /**
     * Perform a model update operation.
	 * 执行一个模型更新操作
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return bool
     */
    protected function performUpdate(Builder $query)
    {
        // If the updating event returns false, we will cancel the update operation so
        // developers can hook Validation systems into their models and cancel this
        // operation if the model does not pass validation. Otherwise, we update.
		// 如果更新事件返回false，我们将取消更新操作。
        if ($this->fireModelEvent('updating') === false) {
            return false;
        }

        // First we need to create a fresh query instance and touch the creation and
        // update timestamp on the model which are maintained by us for developer
        // convenience. Then we will just continue saving the model instances.
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        // Once we have run the update operation, we will fire the "updated" event for
        // this model instance. This will allow developers to hook into these after
        // models are updated, giving them a chance to do any special processing.
        $dirty = $this->getDirty();

        if (count($dirty) > 0) {
            $this->setKeysForSaveQuery($query)->update($dirty);

            $this->syncChanges();

            $this->fireModelEvent('updated', false);
        }

        return true;
    }

    /**
     * Set the keys for a select query.
	 * 为选择查询设置键
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSelectQuery($query)
    {
        $query->where($this->getKeyName(), '=', $this->getKeyForSelectQuery());

        return $query;
    }

    /**
     * Get the primary key value for a select query.
	 * 获取选择查询的主键值
     *
     * @return mixed
     */
    protected function getKeyForSelectQuery()
    {
        return $this->original[$this->getKeyName()] ?? $this->getKey();
    }

    /**
     * Set the keys for a save update query.
	 * 为保存更新查询设置键
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery($query)
    {
        $query->where($this->getKeyName(), '=', $this->getKeyForSaveQuery());

        return $query;
    }

    /**
     * Get the primary key value for a save query.
	 * 获取保存查询的主键值
     *
     * @return mixed
     */
    protected function getKeyForSaveQuery()
    {
        return $this->original[$this->getKeyName()] ?? $this->getKey();
    }

    /**
     * Perform a model insert operation.
	 * 执行模型插入操作
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return bool
     */
    protected function performInsert(Builder $query)
    {
        if ($this->fireModelEvent('creating') === false) {
            return false;
        }

        // First we'll need to create a fresh query instance and touch the creation and
        // update timestamps on this model, which are maintained by us for developer
        // convenience. After, we will just continue saving these model instances.
		// 首先，我们需要创建一个新的查询实例并触摸创建。
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        // If the model has an incrementing key, we can use the "insertGetId" method on
        // the query builder, which will give us back the final inserted ID for this
        // table from the database. Not all tables have to be incrementing though.
        $attributes = $this->getAttributesForInsert();

        if ($this->getIncrementing()) {
            $this->insertAndSetId($query, $attributes);
        }

        // If the table isn't incrementing we'll simply insert these attributes as they
        // are. These attribute arrays must contain an "id" column previously placed
        // there by the developer as the manually determined key for these models.
        else {
            if (empty($attributes)) {
                return true;
            }

            $query->insert($attributes);
        }

        // We will go ahead and set the exists property to true, so that it is set when
        // the created event is fired, just in case the developer tries to update it
        // during the event. This will allow them to do so and run an update here.
        $this->exists = true;

        $this->wasRecentlyCreated = true;

        $this->fireModelEvent('created', false);

        return true;
    }

    /**
     * Insert the given attributes and set the ID on the model.
	 * 插入给定的属性并在模型上设置ID
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $attributes
     * @return void
     */
    protected function insertAndSetId(Builder $query, $attributes)
    {
        $id = $query->insertGetId($attributes, $keyName = $this->getKeyName());

        $this->setAttribute($keyName, $id);
    }

    /**
     * Destroy the models for the given IDs.
	 * 销毁给定id的模型
     *
     * @param  \Illuminate\Support\Collection|array|int|string  $ids
     * @return int
     */
    public static function destroy($ids)
    {
        if ($ids instanceof EloquentCollection) {
            $ids = $ids->modelKeys();
        }

        if ($ids instanceof BaseCollection) {
            $ids = $ids->all();
        }

        $ids = is_array($ids) ? $ids : func_get_args();

        if (count($ids) === 0) {
            return 0;
        }

        // We will actually pull the models from the database table and call delete on
        // each of them individually so that their events get fired properly with a
        // correct set of attributes in case the developers wants to check these.
		// 我们将实际从数据库表中提取模型，并删除每一个都是单独的。
        $key = ($instance = new static)->getKeyName();

        $count = 0;

        foreach ($instance->whereIn($key, $ids)->get() as $model) {
            if ($model->delete()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Delete the model from the database.
	 * 从数据库中删除模型
     *
     * @return bool|null
     *
     * @throws \LogicException
     */
    public function delete()
    {
        $this->mergeAttributesFromCachedCasts();

        if (is_null($this->getKeyName())) {
            throw new LogicException('No primary key defined on model.');
        }

        // If the model doesn't exist, there is nothing to delete so we'll just return
        // immediately and not do anything else. Otherwise, we will continue with a
        // deletion process on the model, firing the proper events, and so forth.
		// 如果模型不存在，则没有什么可删除的。
        if (! $this->exists) {
            return;
        }

        if ($this->fireModelEvent('deleting') === false) {
            return false;
        }

        // Here, we'll touch the owning models, verifying these timestamps get updated
        // for the models. This will allow any caching to get broken on the parents
        // by the timestamp. Then we will go ahead and delete the model instance.
        $this->touchOwners();

        $this->performDeleteOnModel();

        // Once the model has been deleted, we will fire off the deleted event so that
        // the developers may hook into post-delete operations. We will then return
        // a boolean true as the delete is presumably successful on the database.
        $this->fireModelEvent('deleted', false);

        return true;
    }

    /**
     * Delete the model from the database without raising any events.
	 * 在不引发任何事件的情况下从数据库中删除模型
     *
     * @return bool
     */
    public function deleteQuietly()
    {
        return static::withoutEvents(fn () => $this->delete());
    }

    /**
     * Delete the model from the database within a transaction.
	 * 在事务中从数据库中删除模型
     *
     * @return bool|null
     *
     * @throws \Throwable
     */
    public function deleteOrFail()
    {
        if (! $this->exists) {
            return false;
        }

        return $this->getConnection()->transaction(fn () => $this->delete());
    }

    /**
     * Force a hard delete on a soft deleted model.
	 * 对已软删除的模型强制执行硬删除
     *
     * This method protects developers from running forceDelete when the trait is missing.
     *
     * @return bool|null
     */
    public function forceDelete()
    {
        return $this->delete();
    }

    /**
     * Perform the actual delete query on this model instance.
	 * 对这个模型实例执行实际的删除查询
     *
     * @return void
     */
    protected function performDeleteOnModel()
    {
        $this->setKeysForSaveQuery($this->newModelQuery())->delete();

        $this->exists = false;
    }

    /**
     * Begin querying the model.
	 * 开始查询模型
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function query()
    {
        return (new static)->newQuery();
    }

    /**
     * Get a new query builder for the model's table.
	 * 获取模型表的新查询生成器
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQuery()
    {
        return $this->registerGlobalScopes($this->newQueryWithoutScopes());
    }

    /**
     * Get a new query builder that doesn't have any global scopes or eager loading.
	 * 获取一个没有任何全局作用域或主动加载的新查询生成器
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newModelQuery()
    {
        return $this->newEloquentBuilder(
            $this->newBaseQueryBuilder()
        )->setModel($this);
    }

    /**
     * Get a new query builder with no relationships loaded.
	 * 获取没有加载任何关系的新查询生成器
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQueryWithoutRelationships()
    {
        return $this->registerGlobalScopes($this->newModelQuery());
    }

    /**
     * Register the global scopes for this builder instance.
	 * 为此构建器实例注册全局范围
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function registerGlobalScopes($builder)
    {
        foreach ($this->getGlobalScopes() as $identifier => $scope) {
            $builder->withGlobalScope($identifier, $scope);
        }

        return $builder;
    }

    /**
     * Get a new query builder that doesn't have any global scopes.
	 * 获取一个没有任何全局作用域的新查询生成器
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newQueryWithoutScopes()
    {
        return $this->newModelQuery()
            ->with($this->with)
            ->withCount($this->withCount);
    }

    /**
     * Get a new query instance without a given scope.
	 * 获取没有给定范围的新查询实例
     *
     * @param  \Illuminate\Database\Eloquent\Scope|string  $scope
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQueryWithoutScope($scope)
    {
        return $this->newQuery()->withoutGlobalScope($scope);
    }

    /**
     * Get a new query to restore one or more models by their queueable IDs.
	 * 获取一个新查询，根据可排队id还原一个或多个模型。
     *
     * @param  array|int  $ids
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQueryForRestoration($ids)
    {
        return $this->newQueryWithoutScopes()->whereKey($ids);
    }

    /**
     * Create a new Eloquent query builder for the model.
	 * 为模型创建一个新的Eloquent查询构建器
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /**
     * Get a new query builder instance for the connection.
	 * 获取连接的新查询生成器实例
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        return $this->getConnection()->query();
    }

    /**
     * Create a new Eloquent Collection instance.
	 * 创建一个新的Eloquent Collection实例
     *
     * @param  array  $models
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }

    /**
     * Create a new pivot model instance.
	 * 创建一个新的pivot模型实例
     *
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @param  array  $attributes
     * @param  string  $table
     * @param  bool  $exists
     * @param  string|null  $using
     * @return \Illuminate\Database\Eloquent\Relations\Pivot
     */
    public function newPivot(self $parent, array $attributes, $table, $exists, $using = null)
    {
        return $using ? $using::fromRawAttributes($parent, $attributes, $table, $exists)
            : Pivot::fromAttributes($parent, $attributes, $table, $exists);
    }

    /**
     * Determine if the model has a given scope.
	 * 确定模型是否具有给定的范围
     *
     * @param  string  $scope
     * @return bool
     */
    public function hasNamedScope($scope)
    {
        return method_exists($this, 'scope'.ucfirst($scope));
    }

    /**
     * Apply the given named scope if possible.
	 * 如果可能，应用给定的命名作用域。
     *
     * @param  string  $scope
     * @param  array  $parameters
     * @return mixed
     */
    public function callNamedScope($scope, array $parameters = [])
    {
        return $this->{'scope'.ucfirst($scope)}(...$parameters);
    }

    /**
     * Convert the model instance to an array.
	 * 将模型实例转换为数组
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->attributesToArray(), $this->relationsToArray());
    }

    /**
     * Convert the model instance to JSON.
	 * 将模型实例转换为JSON
     *
     * @param  int  $options
     * @return string
     *
     * @throws \Illuminate\Database\Eloquent\JsonEncodingException
     */
    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw JsonEncodingException::forModel($this, json_last_error_msg());
        }

        return $json;
    }

    /**
     * Convert the object into something JSON serializable.
	 * 将对象转换为JSON可序列化的对象
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * Reload a fresh model instance from the database.
	 * 从数据库中重新加载一个新的模型实例
     *
     * @param  array|string  $with
     * @return static|null
     */
    public function fresh($with = [])
    {
        if (! $this->exists) {
            return;
        }

        return $this->setKeysForSelectQuery($this->newQueryWithoutScopes())
            ->useWritePdo()
            ->with(is_string($with) ? func_get_args() : $with)
            ->first();
    }

    /**
     * Reload the current model instance with fresh attributes from the database.
	 * 用数据库中的新属性重新加载当前模型实例
     *
     * @return $this
     */
    public function refresh()
    {
        if (! $this->exists) {
            return $this;
        }

        $this->setRawAttributes(
            $this->setKeysForSelectQuery($this->newQueryWithoutScopes())
                ->useWritePdo()
                ->firstOrFail()
                ->attributes
        );

        $this->load(collect($this->relations)->reject(function ($relation) {
            return $relation instanceof Pivot
                || (is_object($relation) && in_array(AsPivot::class, class_uses_recursive($relation), true));
        })->keys()->all());

        $this->syncOriginal();

        return $this;
    }

    /**
     * Clone the model into a new, non-existing instance.
	 * 将模型克隆到一个新的、不存在的实例中。
     *
     * @param  array|null  $except
     * @return static
     */
    public function replicate(array $except = null)
    {
        $defaults = array_values(array_filter([
            $this->getKeyName(),
            $this->getCreatedAtColumn(),
            $this->getUpdatedAtColumn(),
        ]));

        $attributes = Arr::except(
            $this->getAttributes(), $except ? array_unique(array_merge($except, $defaults)) : $defaults
        );

        return tap(new static, function ($instance) use ($attributes) {
            $instance->setRawAttributes($attributes);

            $instance->setRelations($this->relations);

            $instance->fireModelEvent('replicating', false);
        });
    }

    /**
     * Clone the model into a new, non-existing instance without raising any events.
	 * 将模型克隆到一个新的、不存在的实例中，而不引发任何事件。
     *
     * @param  array|null  $except
     * @return static
     */
    public function replicateQuietly(array $except = null)
    {
        return static::withoutEvents(fn () => $this->replicate($except));
    }

    /**
     * Determine if two models have the same ID and belong to the same table.
	 * 确定两个模型是否具有相同的ID并属于同一表
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @return bool
     */
    public function is($model)
    {
        return ! is_null($model) &&
            $this->getKey() === $model->getKey() &&
            $this->getTable() === $model->getTable() &&
            $this->getConnectionName() === $model->getConnectionName();
    }

    /**
     * Determine if two models are not the same.
	 * 确定两个模型是否不相同
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @return bool
     */
    public function isNot($model)
    {
        return ! $this->is($model);
    }

    /**
     * Get the database connection for the model.
	 * 获取模型的数据库连接
     *
     * @return \Illuminate\Database\Connection
     */
    public function getConnection()
    {
        return static::resolveConnection($this->getConnectionName());
    }

    /**
     * Get the current connection name for the model.
	 * 获取模型的当前连接名称
     *
     * @return string|null
     */
    public function getConnectionName()
    {
        return $this->connection;
    }

    /**
     * Set the connection associated with the model.
	 * 设置与模型关联的连接
     *
     * @param  string|null  $name
     * @return $this
     */
    public function setConnection($name)
    {
        $this->connection = $name;

        return $this;
    }

    /**
     * Resolve a connection instance.
	 * 解析连接实例
     *
     * @param  string|null  $connection
     * @return \Illuminate\Database\Connection
     */
    public static function resolveConnection($connection = null)
    {
        return static::$resolver->connection($connection);
    }

    /**
     * Get the connection resolver instance.
	 * 获取连接解析程序实例
     *
     * @return \Illuminate\Database\ConnectionResolverInterface
     */
    public static function getConnectionResolver()
    {
        return static::$resolver;
    }

    /**
     * Set the connection resolver instance.
	 * 设置连接解析器实例
     *
     * @param  \Illuminate\Database\ConnectionResolverInterface  $resolver
     * @return void
     */
    public static function setConnectionResolver(Resolver $resolver)
    {
        static::$resolver = $resolver;
    }

    /**
     * Unset the connection resolver for models.
	 * 取消设置模型的连接解析器
     *
     * @return void
     */
    public static function unsetConnectionResolver()
    {
        static::$resolver = null;
    }

    /**
     * Get the table associated with the model.
	 * 获取与模型相关联的表
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table ?? Str::snake(Str::pluralStudly(class_basename($this)));
    }

    /**
     * Set the table associated with the model.
	 * 设置与模型相关联的表
     *
     * @param  string  $table
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Get the primary key for the model.
	 * 获取模型的主键
     *
     * @return string
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * Set the primary key for the model.
	 * 为模型设置主键
     *
     * @param  string  $key
     * @return $this
     */
    public function setKeyName($key)
    {
        $this->primaryKey = $key;

        return $this;
    }

    /**
     * Get the table qualified key name.
	 * 获取表限定键名
     *
     * @return string
     */
    public function getQualifiedKeyName()
    {
        return $this->qualifyColumn($this->getKeyName());
    }

    /**
     * Get the auto-incrementing key type.
	 * 获取自动递增的键类型
     *
     * @return string
     */
    public function getKeyType()
    {
        return $this->keyType;
    }

    /**
     * Set the data type for the primary key.
	 * 设置主键的数据类型
     *
     * @param  string  $type
     * @return $this
     */
    public function setKeyType($type)
    {
        $this->keyType = $type;

        return $this;
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
	 * 获取指示id是否在递增的值
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return $this->incrementing;
    }

    /**
     * Set whether IDs are incrementing.
	 * 设置id是否递增
     *
     * @param  bool  $value
     * @return $this
     */
    public function setIncrementing($value)
    {
        $this->incrementing = $value;

        return $this;
    }

    /**
     * Get the value of the model's primary key.
	 * 获取模型主键的值
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->getAttribute($this->getKeyName());
    }

    /**
     * Get the queueable identity for the entity.
	 * 获取实体的可排队标识
     *
     * @return mixed
     */
    public function getQueueableId()
    {
        return $this->getKey();
    }

    /**
     * Get the queueable relationships for the entity.
	 * 获取实体的可排队关系
     *
     * @return array
     */
    public function getQueueableRelations()
    {
        $relations = [];

        foreach ($this->getRelations() as $key => $relation) {
            if (! method_exists($this, $key)) {
                continue;
            }

            $relations[] = $key;

            if ($relation instanceof QueueableCollection) {
                foreach ($relation->getQueueableRelations() as $collectionValue) {
                    $relations[] = $key.'.'.$collectionValue;
                }
            }

            if ($relation instanceof QueueableEntity) {
                foreach ($relation->getQueueableRelations() as $entityValue) {
                    $relations[] = $key.'.'.$entityValue;
                }
            }
        }

        return array_unique($relations);
    }

    /**
     * Get the queueable connection for the entity.
	 * 获取实体的可排队连接
     *
     * @return string|null
     */
    public function getQueueableConnection()
    {
        return $this->getConnectionName();
    }

    /**
     * Get the value of the model's route key.
	 * 获取模型的路由键值
     *
     * @return mixed
     */
    public function getRouteKey()
    {
        return $this->getAttribute($this->getRouteKeyName());
    }

    /**
     * Get the route key for the model.
	 * 获取模型的路由键
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return $this->getKeyName();
    }

    /**
     * Retrieve the model for a bound value.
	 * 检索绑定值的模型
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->resolveRouteBindingQuery($this, $value, $field)->first();
    }

    /**
     * Retrieve the model for a bound value.
	 * 检索绑定值的模型
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveSoftDeletableRouteBinding($value, $field = null)
    {
        return $this->resolveRouteBindingQuery($this, $value, $field)->withTrashed()->first();
    }

    /**
     * Retrieve the child model for a bound value.
	 * 检索绑定值的子模型
     *
     * @param  string  $childType
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveChildRouteBinding($childType, $value, $field)
    {
        return $this->resolveChildRouteBindingQuery($childType, $value, $field)->first();
    }

    /**
     * Retrieve the child model for a bound value.
	 * 检索绑定值的子模型
     *
     * @param  string  $childType
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveSoftDeletableChildRouteBinding($childType, $value, $field)
    {
        return $this->resolveChildRouteBindingQuery($childType, $value, $field)->withTrashed()->first();
    }

    /**
     * Retrieve the child model query for a bound value.
	 * 检索绑定值的子模型查询
     *
     * @param  string  $childType
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    protected function resolveChildRouteBindingQuery($childType, $value, $field)
    {
        $relationship = $this->{$this->childRouteBindingRelationshipName($childType)}();

        $field = $field ?: $relationship->getRelated()->getRouteKeyName();

        if ($relationship instanceof HasManyThrough ||
            $relationship instanceof BelongsToMany) {
            $field = $relationship->getRelated()->getTable().'.'.$field;
        }

        return $relationship instanceof Model
            ? $relationship->resolveRouteBindingQuery($relationship, $value, $field)
            : $relationship->getRelated()->resolveRouteBindingQuery($relationship, $value, $field);
    }

    /**
     * Retrieve the child route model binding relationship name for the given child type.
	 * 检索给定子类型的子路由模型绑定关系名称
     *
     * @param  string  $childType
     * @return string
     */
    protected function childRouteBindingRelationshipName($childType)
    {
        return Str::plural(Str::camel($childType));
    }

    /**
     * Retrieve the model for a bound value.
	 * 检索绑定值的模型
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\Relation  $query
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return $query->where($field ?? $this->getRouteKeyName(), $value);
    }

    /**
     * Get the default foreign key name for the model.
	 * 获取模型的默认外键名
     *
     * @return string
     */
    public function getForeignKey()
    {
        return Str::snake(class_basename($this)).'_'.$this->getKeyName();
    }

    /**
     * Get the number of models to return per page.
	 * 获取每页返回的模型数量
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * Set the number of models to return per page.
	 * 设置每页返回的模型数量
     *
     * @param  int  $perPage
     * @return $this
     */
    public function setPerPage($perPage)
    {
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * Determine if lazy loading is disabled.
	 * 确定是否禁用了延迟加载
     *
     * @return bool
     */
    public static function preventsLazyLoading()
    {
        return static::$modelsShouldPreventLazyLoading;
    }

    /**
     * Determine if discarding guarded attribute fills is disabled.
	 * 确定是否禁用丢弃受保护的属性填充
     *
     * @return bool
     */
    public static function preventsSilentlyDiscardingAttributes()
    {
        return static::$modelsShouldPreventSilentlyDiscardingAttributes;
    }

    /**
     * Determine if accessing missing attributes is disabled.
	 * 确定是否禁用了对缺失属性的访问
     *
     * @return bool
     */
    public static function preventsAccessingMissingAttributes()
    {
        return static::$modelsShouldPreventAccessingMissingAttributes;
    }

    /**
     * Get the broadcast channel route definition that is associated with the given entity.
	 * 获取与给定实体关联的广播通道路由定义
     *
     * @return string
     */
    public function broadcastChannelRoute()
    {
        return str_replace('\\', '.', get_class($this)).'.{'.Str::camel(class_basename($this)).'}';
    }

    /**
     * Get the broadcast channel name that is associated with the given entity.
	 * 获取与给定实体关联的广播通道名称
     *
     * @return string
     */
    public function broadcastChannel()
    {
        return str_replace('\\', '.', get_class($this)).'.'.$this->getKey();
    }

    /**
     * Dynamically retrieve attributes on the model.
	 * 动态检索模型上的属性
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
	 * 动态设置模型上的属性
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if the given attribute exists.
	 * 确定给定属性是否存在
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        try {
            return ! is_null($this->getAttribute($offset));
        } catch (MissingAttributeException) {
            return false;
        }
    }

    /**
     * Get the value for a given offset.
	 * 获取给定偏移量的值
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value for a given offset.
	 * 设置给定偏移量的值
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Unset the value for a given offset.
	 * 取消给定偏移量的值
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset], $this->relations[$offset]);
    }

    /**
     * Determine if an attribute or relation exists on the model.
	 * 确定模型上是否存在属性或关系
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Unset an attribute on the model.
	 * 取消对模型的属性设置
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
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
        if (in_array($method, ['increment', 'decrement'])) {
            return $this->$method(...$parameters);
        }

        if ($resolver = $this->relationResolver(static::class, $method)) {
            return $resolver($this);
        }

        if (Str::startsWith($method, 'through') &&
            method_exists($this, $relationMethod = Str::of($method)->after('through')->lcfirst()->toString())) {
            return $this->through($relationMethod);
        }

        return $this->forwardCallTo($this->newQuery(), $method, $parameters);
    }

    /**
     * Handle dynamic static method calls into the model.
	 * 处理对模型的动态静态方法调用
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }

    /**
     * Convert the model to its string representation.
	 * 将模型转换为其字符串表示形式
     *
     * @return string
     */
    public function __toString()
    {
        return $this->escapeWhenCastingToString
            ? e($this->toJson())
            : $this->toJson();
    }

    /**
     * Indicate that the object's string representation should be escaped when __toString is invoked.
	 * 表明当__toString被调用时，对象的字符串表示应该被转义。
     *
     * @param  bool  $escape
     * @return $this
     */
    public function escapeWhenCastingToString($escape = true)
    {
        $this->escapeWhenCastingToString = $escape;

        return $this;
    }

    /**
     * Prepare the object for serialization.
	 * 为序列化准备对象
     *
     * @return array
     */
    public function __sleep()
    {
        $this->mergeAttributesFromCachedCasts();

        $this->classCastCache = [];
        $this->attributeCastCache = [];

        return array_keys(get_object_vars($this));
    }

    /**
     * When a model is being unserialized, check if it needs to be booted.
	 * 当一个模型被反序列化时，检查它是否需要被引导。
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->bootIfNotBooted();

        $this->initializeTraits();
    }
}
