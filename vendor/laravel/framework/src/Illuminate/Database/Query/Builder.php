<?php
/**
 * Illuminate，数据库，查询，构建者
 */

namespace Illuminate\Database\Query;

use BackedEnum;
use Carbon\CarbonPeriod;
use Closure;
use DateTimeInterface;
use Illuminate\Contracts\Database\Query\Builder as BuilderContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Concerns\BuildsQueries;
use Illuminate\Database\Concerns\ExplainsQueries;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use LogicException;
use RuntimeException;

class Builder implements BuilderContract
{
    use BuildsQueries, ExplainsQueries, ForwardsCalls, Macroable {
        __call as macroCall;
    }

    /**
     * The database connection instance.
	 * 数据库连接实例
     *
     * @var \Illuminate\Database\ConnectionInterface
     */
    public $connection;

    /**
     * The database query grammar instance.
	 * 数据库查询语法实例
     *
     * @var \Illuminate\Database\Query\Grammars\Grammar
     */
    public $grammar;

    /**
     * The database query post processor instance.
	 * 数据库查询后处理器实例
     *
     * @var \Illuminate\Database\Query\Processors\Processor
     */
    public $processor;

    /**
     * The current query value bindings.
	 * 当前查询值绑定
     *
     * @var array
     */
    public $bindings = [
        'select' => [],
        'from' => [],
        'join' => [],
        'where' => [],
        'groupBy' => [],
        'having' => [],
        'order' => [],
        'union' => [],
        'unionOrder' => [],
    ];

    /**
     * An aggregate function and column to be run.
	 * 要运行的聚合函数和列
     *
     * @var array
     */
    public $aggregate;

    /**
     * The columns that should be returned.
	 * 应该返回的列
     *
     * @var array
     */
    public $columns;

    /**
     * Indicates if the query returns distinct results.
	 * 指示查询是否返回不同的结果
     *
     * Occasionally contains the columns that should be distinct.
	 * 偶尔包含应该是不同的列
     *
     * @var bool|array
     */
    public $distinct = false;

    /**
     * The table which the query is targeting.
	 * 查询所针对的表
     *
     * @var string
     */
    public $from;

    /**
     * The index hint for the query.
	 * 查询的索引提示
     *
     * @var \Illuminate\Database\Query\IndexHint
     */
    public $indexHint;

    /**
     * The table joins for the query.
	 * 表连接用于查询
     *
     * @var array
     */
    public $joins;

    /**
     * The where constraints for the query.
	 * 查询的where约束
     *
     * @var array
     */
    public $wheres = [];

    /**
     * The groupings for the query.
	 * 查询的分组
     *
     * @var array
     */
    public $groups;

    /**
     * The having constraints for the query.
	 * 查询的约束条件
     *
     * @var array
     */
    public $havings;

    /**
     * The orderings for the query.
	 * 查询的排序
     *
     * @var array
     */
    public $orders;

    /**
     * The maximum number of records to return.
	 * 要返回的最大记录数
     *
     * @var int
     */
    public $limit;

    /**
     * The number of records to skip.
	 * 要跳过的记录数
     *
     * @var int
     */
    public $offset;

    /**
     * The query union statements.
	 * 查询联合语句
     *
     * @var array
     */
    public $unions;

    /**
     * The maximum number of union records to return.
	 * 要返回的联合记录的最大数目
     *
     * @var int
     */
    public $unionLimit;

    /**
     * The number of union records to skip.
	 * 要跳过的联合记录的数量
     *
     * @var int
     */
    public $unionOffset;

    /**
     * The orderings for the union query.
	 * 联合查询的排序
     *
     * @var array
     */
    public $unionOrders;

    /**
     * Indicates whether row locking is being used.
	 * 指示是否正在使用行锁定
     *
     * @var string|bool
     */
    public $lock;

    /**
     * The callbacks that should be invoked before the query is executed.
	 * 在执行查询之前应该调用的回调函数
     *
     * @var array
     */
    public $beforeQueryCallbacks = [];

    /**
     * All of the available clause operators.
	 * 所有可用的子句操作符
     *
     * @var string[]
     */
    public $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
        'like', 'like binary', 'not like', 'ilike',
        '&', '|', '^', '<<', '>>', '&~', 'is', 'is not',
        'rlike', 'not rlike', 'regexp', 'not regexp',
        '~', '~*', '!~', '!~*', 'similar to',
        'not similar to', 'not ilike', '~~*', '!~~*',
    ];

    /**
     * All of the available bitwise operators.
	 * 所有可用的位操作符
     *
     * @var string[]
     */
    public $bitwiseOperators = [
        '&', '|', '^', '<<', '>>', '&~',
    ];

    /**
     * Whether to use write pdo for the select.
	 * 是否对select操作使用write pdo
     *
     * @var bool
     */
    public $useWritePdo = false;

    /**
     * Create a new query builder instance.
	 * 创建一个新的查询生成器实例
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  \Illuminate\Database\Query\Grammars\Grammar|null  $grammar
     * @param  \Illuminate\Database\Query\Processors\Processor|null  $processor
     * @return void
     */
    public function __construct(ConnectionInterface $connection,
                                Grammar $grammar = null,
                                Processor $processor = null)
    {
        $this->connection = $connection;
        $this->grammar = $grammar ?: $connection->getQueryGrammar();
        $this->processor = $processor ?: $connection->getPostProcessor();
    }

    /**
     * Set the columns to be selected.
	 * 设置要选择的列
     *
     * @param  array|mixed  $columns
     * @return $this
     */
    public function select($columns = ['*'])
    {
        $this->columns = [];
        $this->bindings['select'] = [];

        $columns = is_array($columns) ? $columns : func_get_args();

        foreach ($columns as $as => $column) {
            if (is_string($as) && $this->isQueryable($column)) {
                $this->selectSub($column, $as);
            } else {
                $this->columns[] = $column;
            }
        }

        return $this;
    }

    /**
     * Add a subselect expression to the query.
	 * 向查询添加子选择表达式
     *
     * @param  \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|string  $query
     * @param  string  $as
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function selectSub($query, $as)
    {
        [$query, $bindings] = $this->createSub($query);

        return $this->selectRaw(
            '('.$query.') as '.$this->grammar->wrap($as), $bindings
        );
    }

    /**
     * Add a new "raw" select expression to the query.
	 * 向查询添加一个新的"原始"选择表达式
     *
     * @param  string  $expression
     * @param  array  $bindings
     * @return $this
     */
    public function selectRaw($expression, array $bindings = [])
    {
        $this->addSelect(new Expression($expression));

        if ($bindings) {
            $this->addBinding($bindings, 'select');
        }

        return $this;
    }

    /**
     * Makes "from" fetch from a subquery.
	 * 从子查询中获取"from"
     *
     * @param  \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|string  $query
     * @param  string  $as
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function fromSub($query, $as)
    {
        [$query, $bindings] = $this->createSub($query);

        return $this->fromRaw('('.$query.') as '.$this->grammar->wrapTable($as), $bindings);
    }

    /**
     * Add a raw from clause to the query.
	 * 向查询添加一个原始的from子句
     *
     * @param  string  $expression
     * @param  mixed  $bindings
     * @return $this
     */
    public function fromRaw($expression, $bindings = [])
    {
        $this->from = new Expression($expression);

        $this->addBinding($bindings, 'from');

        return $this;
    }

    /**
     * Creates a subquery and parse it.
	 * 创建一个子查询并解析它
     *
     * @param  \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|string  $query
     * @return array
     */
    protected function createSub($query)
    {
        // If the given query is a Closure, we will execute it while passing in a new
        // query instance to the Closure. This will give the developer a chance to
        // format and work with the query before we cast it to a raw SQL string.
		// 如果给定的查询是一个Closure，我们将在传递new的同时执行它。
        if ($query instanceof Closure) {
            $callback = $query;

            $callback($query = $this->forSubQuery());
        }

        return $this->parseSub($query);
    }

    /**
     * Parse the subquery into SQL and bindings.
	 * 将子查询解析为SQL和绑定
     *
     * @param  mixed  $query
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function parseSub($query)
    {
        if ($query instanceof self || $query instanceof EloquentBuilder || $query instanceof Relation) {
            $query = $this->prependDatabaseNameIfCrossDatabaseQuery($query);

            return [$query->toSql(), $query->getBindings()];
        } elseif (is_string($query)) {
            return [$query, []];
        } else {
            throw new InvalidArgumentException(
                'A subquery must be a query builder instance, a Closure, or a string.'
            );
        }
    }

    /**
     * Prepend the database name if the given query is on another database.
	 * 如果给定的查询在另一个数据库上，则在数据库名称前加上。
     *
     * @param  mixed  $query
     * @return mixed
     */
    protected function prependDatabaseNameIfCrossDatabaseQuery($query)
    {
        if ($query->getConnection()->getDatabaseName() !==
            $this->getConnection()->getDatabaseName()) {
            $databaseName = $query->getConnection()->getDatabaseName();

            if (! str_starts_with($query->from, $databaseName) && ! str_contains($query->from, '.')) {
                $query->from($databaseName.'.'.$query->from);
            }
        }

        return $query;
    }

    /**
     * Add a new select column to the query.
	 * 向查询添加一个新的选择列
     *
     * @param  array|mixed  $column
     * @return $this
     */
    public function addSelect($column)
    {
        $columns = is_array($column) ? $column : func_get_args();

        foreach ($columns as $as => $column) {
            if (is_string($as) && $this->isQueryable($column)) {
                if (is_null($this->columns)) {
                    $this->select($this->from.'.*');
                }

                $this->selectSub($column, $as);
            } else {
                if (is_array($this->columns) && in_array($column, $this->columns, true)) {
                    continue;
                }

                $this->columns[] = $column;
            }
        }

        return $this;
    }

    /**
     * Force the query to only return distinct results.
	 * 强制查询只返回不同的结果
     *
     * @return $this
     */
    public function distinct()
    {
        $columns = func_get_args();

        if (count($columns) > 0) {
            $this->distinct = is_array($columns[0]) || is_bool($columns[0]) ? $columns[0] : $columns;
        } else {
            $this->distinct = true;
        }

        return $this;
    }

    /**
     * Set the table which the query is targeting.
	 * 设置查询所针对的表
     *
     * @param  \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|string  $table
     * @param  string|null  $as
     * @return $this
     */
    public function from($table, $as = null)
    {
        if ($this->isQueryable($table)) {
            return $this->fromSub($table, $as);
        }

        $this->from = $as ? "{$table} as {$as}" : $table;

        return $this;
    }

    /**
     * Add an index hint to suggest a query index.
	 * 添加索引提示以建议查询索引
     *
     * @param  string  $index
     * @return $this
     */
    public function useIndex($index)
    {
        $this->indexHint = new IndexHint('hint', $index);

        return $this;
    }

    /**
     * Add an index hint to force a query index.
	 * 添加索引提示来强制查询索引
     *
     * @param  string  $index
     * @return $this
     */
    public function forceIndex($index)
    {
        $this->indexHint = new IndexHint('force', $index);

        return $this;
    }

    /**
     * Add an index hint to ignore a query index.
	 * 添加索引提示以忽略查询索引
     *
     * @param  string  $index
     * @return $this
     */
    public function ignoreIndex($index)
    {
        $this->indexHint = new IndexHint('ignore', $index);

        return $this;
    }

    /**
     * Add a join clause to the query.
	 * 向查询添加连接子句
     *
     * @param  string  $table
     * @param  \Closure|string  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * @param  string  $type
     * @param  bool  $where
     * @return $this
     */
    public function join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
    {
        $join = $this->newJoinClause($this, $type, $table);

        // If the first "column" of the join is really a Closure instance the developer
        // is trying to build a join with a complex "on" clause containing more than
        // one condition, so we'll add the join and call a Closure with the query.
		// 如果连接的第一个"列"确实是一个Closure实例，则开发人员可以。
        if ($first instanceof Closure) {
            $first($join);

            $this->joins[] = $join;

            $this->addBinding($join->getBindings(), 'join');
        }

        // If the column is simply a string, we can assume the join simply has a basic
        // "on" clause with a single condition. So we will just build the join with
        // this simple join clauses attached to it. There is not a join callback.
        else {
            $method = $where ? 'where' : 'on';

            $this->joins[] = $join->$method($first, $operator, $second);

            $this->addBinding($join->getBindings(), 'join');
        }

        return $this;
    }

    /**
     * Add a "join where" clause to the query.
	 * 向查询添加"join where"子句
     *
     * @param  string  $table
     * @param  \Closure|string  $first
     * @param  string  $operator
     * @param  string  $second
     * @param  string  $type
     * @return $this
     */
    public function joinWhere($table, $first, $operator, $second, $type = 'inner')
    {
        return $this->join($table, $first, $operator, $second, $type, true);
    }

    /**
     * Add a subquery join clause to the query.
	 * 向查询添加子查询连接子句
     *
     * @param  \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|string  $query
     * @param  string  $as
     * @param  \Closure|string  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * @param  string  $type
     * @param  bool  $where
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
    {
        [$query, $bindings] = $this->createSub($query);

        $expression = '('.$query.') as '.$this->grammar->wrapTable($as);

        $this->addBinding($bindings, 'join');

        return $this->join(new Expression($expression), $first, $operator, $second, $type, $where);
    }

    /**
     * Add a left join to the query.
	 * 向查询添加左连接
     *
     * @param  string  $table
     * @param  \Closure|string  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * @return $this
     */
    public function leftJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'left');
    }

    /**
     * Add a "join where" clause to the query.
	 * 向查询添加"join where"子句
     *
     * @param  string  $table
     * @param  \Closure|string  $first
     * @param  string  $operator
     * @param  string  $second
     * @return $this
     */
    public function leftJoinWhere($table, $first, $operator, $second)
    {
        return $this->joinWhere($table, $first, $operator, $second, 'left');
    }

    /**
     * Add a subquery left join to the query.
	 * 向查询添加子查询左连接
     *
     * @param  \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|string  $query
     * @param  string  $as
     * @param  \Closure|string  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * @return $this
     */
    public function leftJoinSub($query, $as, $first, $operator = null, $second = null)
    {
        return $this->joinSub($query, $as, $first, $operator, $second, 'left');
    }

    /**
     * Add a right join to the query.
	 * 向查询添加右连接
     *
     * @param  string  $table
     * @param  \Closure|string  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * @return $this
     */
    public function rightJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'right');
    }

    /**
     * Add a "right join where" clause to the query.
	 * 向查询添加"right join where"子句
     *
     * @param  string  $table
     * @param  \Closure|string  $first
     * @param  string  $operator
     * @param  string  $second
     * @return $this
     */
    public function rightJoinWhere($table, $first, $operator, $second)
    {
        return $this->joinWhere($table, $first, $operator, $second, 'right');
    }

    /**
     * Add a subquery right join to the query.
	 * 向查询添加子查询右连接
     *
     * @param  \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|string  $query
     * @param  string  $as
     * @param  \Closure|string  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * @return $this
     */
    public function rightJoinSub($query, $as, $first, $operator = null, $second = null)
    {
        return $this->joinSub($query, $as, $first, $operator, $second, 'right');
    }

    /**
     * Add a "cross join" clause to the query.
	 * 向查询添加"交叉连接"子句
     *
     * @param  string  $table
     * @param  \Closure|string|null  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * @return $this
     */
    public function crossJoin($table, $first = null, $operator = null, $second = null)
    {
        if ($first) {
            return $this->join($table, $first, $operator, $second, 'cross');
        }

        $this->joins[] = $this->newJoinClause($this, 'cross', $table);

        return $this;
    }

    /**
     * Add a subquery cross join to the query.
	 * 向查询添加子查询交叉连接
     *
     * @param  \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|string  $query
     * @param  string  $as
     * @return $this
     */
    public function crossJoinSub($query, $as)
    {
        [$query, $bindings] = $this->createSub($query);

        $expression = '('.$query.') as '.$this->grammar->wrapTable($as);

        $this->addBinding($bindings, 'join');

        $this->joins[] = $this->newJoinClause($this, 'cross', new Expression($expression));

        return $this;
    }

    /**
     * Get a new join clause.
	 * 获取一个新的连接子句
     *
     * @param  \Illuminate\Database\Query\Builder  $parentQuery
     * @param  string  $type
     * @param  string  $table
     * @return \Illuminate\Database\Query\JoinClause
     */
    protected function newJoinClause(self $parentQuery, $type, $table)
    {
        return new JoinClause($parentQuery, $type, $table);
    }

    /**
     * Merge an array of where clauses and bindings.
	 * 合并where子句和绑定的数组
     *
     * @param  array  $wheres
     * @param  array  $bindings
     * @return $this
     */
    public function mergeWheres($wheres, $bindings)
    {
        $this->wheres = array_merge($this->wheres, (array) $wheres);

        $this->bindings['where'] = array_values(
            array_merge($this->bindings['where'], (array) $bindings)
        );

        return $this;
    }

    /**
     * Add a basic where clause to the query.
	 * 向查询添加一个基本的where子句
     *
     * @param  \Closure|string|array  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        // If the column is an array, we will assume it is an array of key-value pairs
        // and can add them each as a where clause. We will maintain the boolean we
        // received when the method was called and pass it into the nested where.
		// 如果列是一个数组，我们将假定它是一个键值对数组。
        if (is_array($column)) {
            return $this->addArrayOfWheres($column, $boolean);
        }

        // Here we will make some assumptions about the operator. If only 2 values are
        // passed to the method, we will assume that the operator is an equals sign
        // and keep going. Otherwise, we'll require the operator to be passed in.
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        // If the column is actually a Closure instance, we will assume the developer
        // wants to begin a nested where statement which is wrapped in parentheses.
        // We will add that Closure to the query and return back out immediately.
        if ($column instanceof Closure && is_null($operator)) {
            return $this->whereNested($column, $boolean);
        }

        // If the column is a Closure instance and there is an operator value, we will
        // assume the developer wants to run a subquery and then compare the result
        // of that subquery with the given value that was provided to the method.
        if ($this->isQueryable($column) && ! is_null($operator)) {
            [$sub, $bindings] = $this->createSub($column);

            return $this->addBinding($bindings, 'where')
                ->where(new Expression('('.$sub.')'), $operator, $value, $boolean);
        }

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }

        // If the value is a Closure, it means the developer is performing an entire
        // sub-select within the query and we will need to compile the sub-select
        // within the where clause to get the appropriate query record results.
        if ($value instanceof Closure) {
            return $this->whereSub($column, $operator, $value, $boolean);
        }

        // If the value is "null", we will just assume the developer wants to add a
        // where null clause to the query. So, we will allow a short-cut here to
        // that method for convenience so the developer doesn't have to check.
        if (is_null($value)) {
            return $this->whereNull($column, $boolean, $operator !== '=');
        }

        $type = 'Basic';

        // If the column is making a JSON reference we'll check to see if the value
        // is a boolean. If it is, we'll add the raw boolean string as an actual
        // value to the query to ensure this is properly handled by the query.
        if (str_contains($column, '->') && is_bool($value)) {
            $value = new Expression($value ? 'true' : 'false');

            if (is_string($column)) {
                $type = 'JsonBoolean';
            }
        }

        if ($this->isBitwiseOperator($operator)) {
            $type = 'Bitwise';
        }

        // Now that we are working with just a simple query we can put the elements
        // in our array and add the query binding to our array of bindings that
        // will be bound to each SQL statements when it is finally executed.
        $this->wheres[] = compact(
            'type', 'column', 'operator', 'value', 'boolean'
        );

        if (! $value instanceof Expression) {
            $this->addBinding($this->flattenValue($value), 'where');
        }

        return $this;
    }

    /**
     * Add an array of where clauses to the query.
	 * 向查询添加where子句数组
     *
     * @param  array  $column
     * @param  string  $boolean
     * @param  string  $method
     * @return $this
     */
    protected function addArrayOfWheres($column, $boolean, $method = 'where')
    {
        return $this->whereNested(function ($query) use ($column, $method, $boolean) {
            foreach ($column as $key => $value) {
                if (is_numeric($key) && is_array($value)) {
                    $query->{$method}(...array_values($value));
                } else {
                    $query->{$method}($key, '=', $value, $boolean);
                }
            }
        }, $boolean);
    }

    /**
     * Prepare the value and operator for a where clause.
	 * 为where子句准备值和操作符
     *
     * @param  string  $value
     * @param  string  $operator
     * @param  bool  $useDefault
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function prepareValueAndOperator($value, $operator, $useDefault = false)
    {
        if ($useDefault) {
            return [$operator, '='];
        } elseif ($this->invalidOperatorAndValue($operator, $value)) {
            throw new InvalidArgumentException('Illegal operator and value combination.');
        }

        return [$value, $operator];
    }

    /**
     * Determine if the given operator and value combination is legal.
	 * 确定给定的操作符和值组合是否合法
     *
     * Prevents using Null values with invalid operators.
     *
     * @param  string  $operator
     * @param  mixed  $value
     * @return bool
     */
    protected function invalidOperatorAndValue($operator, $value)
    {
        return is_null($value) && in_array($operator, $this->operators) &&
             ! in_array($operator, ['=', '<>', '!=']);
    }

    /**
     * Determine if the given operator is supported.
	 * 确定是否支持给定的操作符
     *
     * @param  string  $operator
     * @return bool
     */
    protected function invalidOperator($operator)
    {
        return ! is_string($operator) || (! in_array(strtolower($operator), $this->operators, true) &&
               ! in_array(strtolower($operator), $this->grammar->getOperators(), true));
    }

    /**
     * Determine if the operator is a bitwise operator.
	 * 确定操作符是否是位操作符
     *
     * @param  string  $operator
     * @return bool
     */
    protected function isBitwiseOperator($operator)
    {
        return in_array(strtolower($operator), $this->bitwiseOperators, true) ||
               in_array(strtolower($operator), $this->grammar->getBitwiseOperators(), true);
    }

    /**
     * Add an "or where" clause to the query.
	 * 向查询添加"or where"子句
     *
     * @param  \Closure|string|array  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return $this
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->where($column, $operator, $value, 'or');
    }

    /**
     * Add a basic "where not" clause to the query.
	 * 在查询中添加一个基本的"where not"子句
     *
     * @param  \Closure|string|array  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * @return $this
     */
    public function whereNot($column, $operator = null, $value = null, $boolean = 'and')
    {
        if (is_array($column)) {
            return $this->whereNested(function ($query) use ($column, $operator, $value, $boolean) {
                $query->where($column, $operator, $value, $boolean);
            }, $boolean.' not');
        }

        return $this->where($column, $operator, $value, $boolean.' not');
    }

    /**
     * Add an "or where not" clause to the query.
	 * 在查询中添加"or where not"子句
     *
     * @param  \Closure|string|array  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return $this
     */
    public function orWhereNot($column, $operator = null, $value = null)
    {
        return $this->whereNot($column, $operator, $value, 'or');
    }

    /**
     * Add a "where" clause comparing two columns to the query.
	 * 添加一个"where"子句，比较查询中的两列。
     *
     * @param  string|array  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * @param  string|null  $boolean
     * @return $this
     */
    public function whereColumn($first, $operator = null, $second = null, $boolean = 'and')
    {
        // If the column is an array, we will assume it is an array of key-value pairs
        // and can add them each as a where clause. We will maintain the boolean we
        // received when the method was called and pass it into the nested where.
		// 如果列是一个数组，我们将假定它是一个键值对数组。
        if (is_array($first)) {
            return $this->addArrayOfWheres($first, $boolean, 'whereColumn');
        }

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            [$second, $operator] = [$operator, '='];
        }

        // Finally, we will add this where clause into this array of clauses that we
        // are building for the query. All of them will be compiled via a grammar
        // once the query is about to be executed and run against the database.
        $type = 'Column';

        $this->wheres[] = compact(
            'type', 'first', 'operator', 'second', 'boolean'
        );

        return $this;
    }

    /**
     * Add an "or where" clause comparing two columns to the query.
	 * 添加"or where"子句，将两个列与查询进行比较。
     *
     * @param  string|array  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * @return $this
     */
    public function orWhereColumn($first, $operator = null, $second = null)
    {
        return $this->whereColumn($first, $operator, $second, 'or');
    }

    /**
     * Add a raw where clause to the query.
	 * 向查询添加一个原始的where子句
     *
     * @param  string  $sql
     * @param  mixed  $bindings
     * @param  string  $boolean
     * @return $this
     */
    public function whereRaw($sql, $bindings = [], $boolean = 'and')
    {
        $this->wheres[] = ['type' => 'raw', 'sql' => $sql, 'boolean' => $boolean];

        $this->addBinding((array) $bindings, 'where');

        return $this;
    }

    /**
     * Add a raw or where clause to the query.
	 * 向查询添加raw或where子句
     *
     * @param  string  $sql
     * @param  mixed  $bindings
     * @return $this
     */
    public function orWhereRaw($sql, $bindings = [])
    {
        return $this->whereRaw($sql, $bindings, 'or');
    }

    /**
     * Add a "where in" clause to the query.
	 * 向查询添加"where in"子句
     *
     * @param  string  $column
     * @param  mixed  $values
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotIn' : 'In';

        // If the value is a query builder instance we will assume the developer wants to
        // look for any values that exist within this given query. So, we will add the
        // query accordingly so that this query is properly executed when it is run.
		// 如果该值是一个查询生成器实例，我们将假设开发人员希望这样做。
        if ($this->isQueryable($values)) {
            [$query, $bindings] = $this->createSub($values);

            $values = [new Expression($query)];

            $this->addBinding($bindings, 'where');
        }

        // Next, if the value is Arrayable we need to cast it to its raw array form so we
        // have the underlying array value instead of an Arrayable object which is not
        // able to be added as a binding, etc. We will then add to the wheres array.
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        $this->wheres[] = compact('type', 'column', 'values', 'boolean');

        if (count($values) !== count(Arr::flatten($values, 1))) {
            throw new InvalidArgumentException('Nested arrays may not be passed to whereIn method.');
        }

        // Finally, we'll add a binding for each value unless that value is an expression
        // in which case we will just skip over it since it will be the query as a raw
        // string and not as a parameterized place-holder to be replaced by the PDO.
        $this->addBinding($this->cleanBindings($values), 'where');

        return $this;
    }

    /**
     * Add an "or where in" clause to the query.
	 * 在查询中添加"or where in"子句
     *
     * @param  string  $column
     * @param  mixed  $values
     * @return $this
     */
    public function orWhereIn($column, $values)
    {
        return $this->whereIn($column, $values, 'or');
    }

    /**
     * Add a "where not in" clause to the query.
	 * 在查询中添加"where not in"子句
     *
     * @param  string  $column
     * @param  mixed  $values
     * @param  string  $boolean
     * @return $this
     */
    public function whereNotIn($column, $values, $boolean = 'and')
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    /**
     * Add an "or where not in" clause to the query.
	 * 在查询中添加"or where not in"子句
     *
     * @param  string  $column
     * @param  mixed  $values
     * @return $this
     */
    public function orWhereNotIn($column, $values)
    {
        return $this->whereNotIn($column, $values, 'or');
    }

    /**
     * Add a "where in raw" clause for integer values to the query.
	 * 在查询中为整数值添加"where in raw"子句
     *
     * @param  string  $column
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $values
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function whereIntegerInRaw($column, $values, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotInRaw' : 'InRaw';

        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        $values = Arr::flatten($values);

        foreach ($values as &$value) {
            $value = (int) $value;
        }

        $this->wheres[] = compact('type', 'column', 'values', 'boolean');

        return $this;
    }

    /**
     * Add an "or where in raw" clause for integer values to the query.
	 * 向查询中添加整数值的"or where in raw"子句
     *
     * @param  string  $column
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $values
     * @return $this
     */
    public function orWhereIntegerInRaw($column, $values)
    {
        return $this->whereIntegerInRaw($column, $values, 'or');
    }

    /**
     * Add a "where not in raw" clause for integer values to the query.
	 * 在查询中为整数值添加"where not in raw"子句
     *
     * @param  string  $column
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $values
     * @param  string  $boolean
     * @return $this
     */
    public function whereIntegerNotInRaw($column, $values, $boolean = 'and')
    {
        return $this->whereIntegerInRaw($column, $values, $boolean, true);
    }

    /**
     * Add an "or where not in raw" clause for integer values to the query.
	 * 在查询中为整数值添加"or where not in raw"子句
     *
     * @param  string  $column
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $values
     * @return $this
     */
    public function orWhereIntegerNotInRaw($column, $values)
    {
        return $this->whereIntegerNotInRaw($column, $values, 'or');
    }

    /**
     * Add a "where null" clause to the query.
	 * 向查询添加"where null"子句
     *
     * @param  string|array  $columns
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function whereNull($columns, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotNull' : 'Null';

        foreach (Arr::wrap($columns) as $column) {
            $this->wheres[] = compact('type', 'column', 'boolean');
        }

        return $this;
    }

    /**
     * Add an "or where null" clause to the query.
	 * 向查询添加"or where null"子句
     *
     * @param  string|array  $column
     * @return $this
     */
    public function orWhereNull($column)
    {
        return $this->whereNull($column, 'or');
    }

    /**
     * Add a "where not null" clause to the query.
	 * 在查询中添加"where not null"子句
     *
     * @param  string|array  $columns
     * @param  string  $boolean
     * @return $this
     */
    public function whereNotNull($columns, $boolean = 'and')
    {
        return $this->whereNull($columns, $boolean, true);
    }

    /**
     * Add a where between statement to the query.
	 * 向查询添加where between语句
     *
     * @param  string|\Illuminate\Database\Query\Expression  $column
     * @param  iterable  $values
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function whereBetween($column, iterable $values, $boolean = 'and', $not = false)
    {
        $type = 'between';

        if ($values instanceof CarbonPeriod) {
            $values = $values->toArray();
        }

        $this->wheres[] = compact('type', 'column', 'values', 'boolean', 'not');

        $this->addBinding(array_slice($this->cleanBindings(Arr::flatten($values)), 0, 2), 'where');

        return $this;
    }

    /**
     * Add a where between statement using columns to the query.
	 * 使用列向查询添加where between语句
     *
     * @param  string  $column
     * @param  array  $values
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function whereBetweenColumns($column, array $values, $boolean = 'and', $not = false)
    {
        $type = 'betweenColumns';

        $this->wheres[] = compact('type', 'column', 'values', 'boolean', 'not');

        return $this;
    }

    /**
     * Add an or where between statement to the query.
	 * 在查询中添加or where between语句
     *
     * @param  string  $column
     * @param  iterable  $values
     * @return $this
     */
    public function orWhereBetween($column, iterable $values)
    {
        return $this->whereBetween($column, $values, 'or');
    }

    /**
     * Add an or where between statement using columns to the query.
	 * 使用列向查询添加or where between语句
     *
     * @param  string  $column
     * @param  array  $values
     * @return $this
     */
    public function orWhereBetweenColumns($column, array $values)
    {
        return $this->whereBetweenColumns($column, $values, 'or');
    }

    /**
     * Add a where not between statement to the query.
	 * 向查询添加where not between语句
     *
     * @param  string  $column
     * @param  iterable  $values
     * @param  string  $boolean
     * @return $this
     */
    public function whereNotBetween($column, iterable $values, $boolean = 'and')
    {
        return $this->whereBetween($column, $values, $boolean, true);
    }

    /**
     * Add a where not between statement using columns to the query.
	 * 使用列向查询添加where not between语句
     *
     * @param  string  $column
     * @param  array  $values
     * @param  string  $boolean
     * @return $this
     */
    public function whereNotBetweenColumns($column, array $values, $boolean = 'and')
    {
        return $this->whereBetweenColumns($column, $values, $boolean, true);
    }

    /**
     * Add an or where not between statement to the query.
	 * 在查询中添加or where not between语句
     *
     * @param  string  $column
     * @param  iterable  $values
     * @return $this
     */
    public function orWhereNotBetween($column, iterable $values)
    {
        return $this->whereNotBetween($column, $values, 'or');
    }

    /**
     * Add an or where not between statement using columns to the query.
	 * 在查询中使用列的语句之间添加or where not
     *
     * @param  string  $column
     * @param  array  $values
     * @return $this
     */
    public function orWhereNotBetweenColumns($column, array $values)
    {
        return $this->whereNotBetweenColumns($column, $values, 'or');
    }

    /**
     * Add an "or where not null" clause to the query.
	 * 在查询中添加一个"or where not null"子句
     *
     * @param  string  $column
     * @return $this
     */
    public function orWhereNotNull($column)
    {
        return $this->whereNotNull($column, 'or');
    }

    /**
     * Add a "where date" statement to the query.
	 * 向查询添加"where date"语句
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * @param  string  $boolean
     * @return $this
     */
    public function whereDate($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $value = $this->flattenValue($value);

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d');
        }

        return $this->addDateBasedWhere('Date', $column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where date" statement to the query.
	 * 向查询添加"or where dat"语句
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * @return $this
     */
    public function orWhereDate($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->whereDate($column, $operator, $value, 'or');
    }

    /**
     * Add a "where time" statement to the query.
	 * 向查询添加"where time"语句
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * @param  string  $boolean
     * @return $this
     */
    public function whereTime($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $value = $this->flattenValue($value);

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('H:i:s');
        }

        return $this->addDateBasedWhere('Time', $column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where time" statement to the query.
	 * 在查询中添加"or where time"语句
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * @return $this
     */
    public function orWhereTime($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->whereTime($column, $operator, $value, 'or');
    }

    /**
     * Add a "where day" statement to the query.
	 * 向查询添加"where day"语句
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|int|null  $value
     * @param  string  $boolean
     * @return $this
     */
    public function whereDay($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $value = $this->flattenValue($value);

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('d');
        }

        if (! $value instanceof Expression) {
            $value = sprintf('%02d', $value);
        }

        return $this->addDateBasedWhere('Day', $column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where day" statement to the query.
	 * 向查询中添加"or where day"语句
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|int|null  $value
     * @return $this
     */
    public function orWhereDay($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->whereDay($column, $operator, $value, 'or');
    }

    /**
     * Add a "where month" statement to the query.
	 * 向查询添加"where month"语句
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|int|null  $value
     * @param  string  $boolean
     * @return $this
     */
    public function whereMonth($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $value = $this->flattenValue($value);

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('m');
        }

        if (! $value instanceof Expression) {
            $value = sprintf('%02d', $value);
        }

        return $this->addDateBasedWhere('Month', $column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where month" statement to the query.
	 * 向查询中添加"or where month"语句
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|int|null  $value
     * @return $this
     */
    public function orWhereMonth($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->whereMonth($column, $operator, $value, 'or');
    }

    /**
     * Add a "where year" statement to the query.
	 * 向查询中添加"where year"语句
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|int|null  $value
     * @param  string  $boolean
     * @return $this
     */
    public function whereYear($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $value = $this->flattenValue($value);

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y');
        }

        return $this->addDateBasedWhere('Year', $column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where year" statement to the query.
	 * 向查询中添加"or where year"语句
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|int|null  $value
     * @return $this
     */
    public function orWhereYear($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->whereYear($column, $operator, $value, 'or');
    }

    /**
     * Add a date based (year, month, day, time) statement to the query.
	 * 向查询添加基于日期（年、月、日、时间）的语句
     *
     * @param  string  $type
     * @param  string  $column
     * @param  string  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * @return $this
     */
    protected function addDateBasedWhere($type, $column, $operator, $value, $boolean = 'and')
    {
        $this->wheres[] = compact('column', 'type', 'boolean', 'operator', 'value');

        if (! $value instanceof Expression) {
            $this->addBinding($value, 'where');
        }

        return $this;
    }

    /**
     * Add a nested where statement to the query.
	 * 向查询添加嵌套的where语句
     *
     * @param  \Closure  $callback
     * @param  string  $boolean
     * @return $this
     */
    public function whereNested(Closure $callback, $boolean = 'and')
    {
        $callback($query = $this->forNestedWhere());

        return $this->addNestedWhereQuery($query, $boolean);
    }

    /**
     * Create a new query instance for nested where condition.
	 * 为嵌套where条件创建一个新的查询实例
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function forNestedWhere()
    {
        return $this->newQuery()->from($this->from);
    }

    /**
     * Add another query builder as a nested where to the query builder.
	 * 将另一个查询构建器添加为查询构建器的嵌套位置
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string  $boolean
     * @return $this
     */
    public function addNestedWhereQuery($query, $boolean = 'and')
    {
        if (count($query->wheres)) {
            $type = 'Nested';

            $this->wheres[] = compact('type', 'query', 'boolean');

            $this->addBinding($query->getRawBindings()['where'], 'where');
        }

        return $this;
    }

    /**
     * Add a full sub-select to the query.
	 * 向查询添加一个完整的子选择
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  \Closure  $callback
     * @param  string  $boolean
     * @return $this
     */
    protected function whereSub($column, $operator, Closure $callback, $boolean)
    {
        $type = 'Sub';

        // Once we have the query instance we can simply execute it so it can add all
        // of the sub-select's conditions to itself, and then we can cache it off
        // in the array of where clauses for the "main" parent query instance.
		// 一旦我们有了查询实例，我们可以简单地执行它，这样它就可以添加所有。
        $callback($query = $this->forSubQuery());

        $this->wheres[] = compact(
            'type', 'column', 'operator', 'query', 'boolean'
        );

        $this->addBinding($query->getBindings(), 'where');

        return $this;
    }

    /**
     * Add an exists clause to the query.
	 * 向查询添加exists子句
     *
     * @param  \Closure  $callback
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function whereExists(Closure $callback, $boolean = 'and', $not = false)
    {
        $query = $this->forSubQuery();

        // Similar to the sub-select clause, we will create a new query instance so
        // the developer may cleanly specify the entire exists query and we will
        // compile the whole thing in the grammar and insert it into the SQL.
		// 与子选择子句类似，我们将这样创建一个新的查询实例。
        $callback($query);

        return $this->addWhereExistsQuery($query, $boolean, $not);
    }

    /**
     * Add an or exists clause to the query.
	 * 向查询添加or exists子句
     *
     * @param  \Closure  $callback
     * @param  bool  $not
     * @return $this
     */
    public function orWhereExists(Closure $callback, $not = false)
    {
        return $this->whereExists($callback, 'or', $not);
    }

    /**
     * Add a where not exists clause to the query.
	 * 向查询添加where not exists子句
     *
     * @param  \Closure  $callback
     * @param  string  $boolean
     * @return $this
     */
    public function whereNotExists(Closure $callback, $boolean = 'and')
    {
        return $this->whereExists($callback, $boolean, true);
    }

    /**
     * Add a where not exists clause to the query.
	 * 向查询添加where not exists子句
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function orWhereNotExists(Closure $callback)
    {
        return $this->orWhereExists($callback, true);
    }

    /**
     * Add an exists clause to the query.
	 * 向查询添加exists子句
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function addWhereExistsQuery(self $query, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotExists' : 'Exists';

        $this->wheres[] = compact('type', 'query', 'boolean');

        $this->addBinding($query->getBindings(), 'where');

        return $this;
    }

    /**
     * Adds a where condition using row values.
	 * 使用行值添加where条件
     *
     * @param  array  $columns
     * @param  string  $operator
     * @param  array  $values
     * @param  string  $boolean
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function whereRowValues($columns, $operator, $values, $boolean = 'and')
    {
        if (count($columns) !== count($values)) {
            throw new InvalidArgumentException('The number of columns must match the number of values');
        }

        $type = 'RowValues';

        $this->wheres[] = compact('type', 'columns', 'operator', 'values', 'boolean');

        $this->addBinding($this->cleanBindings($values));

        return $this;
    }

    /**
     * Adds an or where condition using row values.
	 * 使用行值添加或where条件
     *
     * @param  array  $columns
     * @param  string  $operator
     * @param  array  $values
     * @return $this
     */
    public function orWhereRowValues($columns, $operator, $values)
    {
        return $this->whereRowValues($columns, $operator, $values, 'or');
    }

    /**
     * Add a "where JSON contains" clause to the query.
	 * 向查询添加"where JSON contains"子句
     *
     * @param  string  $column
     * @param  mixed  $value
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function whereJsonContains($column, $value, $boolean = 'and', $not = false)
    {
        $type = 'JsonContains';

        $this->wheres[] = compact('type', 'column', 'value', 'boolean', 'not');

        if (! $value instanceof Expression) {
            $this->addBinding($this->grammar->prepareBindingForJsonContains($value));
        }

        return $this;
    }

    /**
     * Add an "or where JSON contains" clause to the query.
	 * 向查询中添加"or where JSON contains"子句
     *
     * @param  string  $column
     * @param  mixed  $value
     * @return $this
     */
    public function orWhereJsonContains($column, $value)
    {
        return $this->whereJsonContains($column, $value, 'or');
    }

    /**
     * Add a "where JSON not contains" clause to the query.
	 * 在查询中添加"where JSON not contains"子句
     *
     * @param  string  $column
     * @param  mixed  $value
     * @param  string  $boolean
     * @return $this
     */
    public function whereJsonDoesntContain($column, $value, $boolean = 'and')
    {
        return $this->whereJsonContains($column, $value, $boolean, true);
    }

    /**
     * Add an "or where JSON not contains" clause to the query.
	 * 在查询中添加"or where JSON not contains"子句
     *
     * @param  string  $column
     * @param  mixed  $value
     * @return $this
     */
    public function orWhereJsonDoesntContain($column, $value)
    {
        return $this->whereJsonDoesntContain($column, $value, 'or');
    }

    /**
     * Add a clause that determines if a JSON path exists to the query.
	 * 添加一个子句，用于确定查询是否存在JSON路径。
     *
     * @param  string  $column
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function whereJsonContainsKey($column, $boolean = 'and', $not = false)
    {
        $type = 'JsonContainsKey';

        $this->wheres[] = compact('type', 'column', 'boolean', 'not');

        return $this;
    }

    /**
     * Add an "or" clause that determines if a JSON path exists to the query.
	 * 添加一个"or"子句，用于确定查询是否存在JSON路径。
     *
     * @param  string  $column
     * @return $this
     */
    public function orWhereJsonContainsKey($column)
    {
        return $this->whereJsonContainsKey($column, 'or');
    }

    /**
     * Add a clause that determines if a JSON path does not exist to the query.
	 * 向查询添加一个子句，用于确定JSON路径是否不存在。
     *
     * @param  string  $column
     * @param  string  $boolean
     * @return $this
     */
    public function whereJsonDoesntContainKey($column, $boolean = 'and')
    {
        return $this->whereJsonContainsKey($column, $boolean, true);
    }

    /**
     * Add an "or" clause that determines if a JSON path does not exist to the query.
	 * 向查询添加一个"or"子句，用于确定JSON路径是否不存在。
     *
     * @param  string  $column
     * @return $this
     */
    public function orWhereJsonDoesntContainKey($column)
    {
        return $this->whereJsonDoesntContainKey($column, 'or');
    }

    /**
     * Add a "where JSON length" clause to the query.
	 * 向查询添加"where JSON length"子句
     *
     * @param  string  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * @return $this
     */
    public function whereJsonLength($column, $operator, $value = null, $boolean = 'and')
    {
        $type = 'JsonLength';

        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $this->wheres[] = compact('type', 'column', 'operator', 'value', 'boolean');

        if (! $value instanceof Expression) {
            $this->addBinding((int) $this->flattenValue($value));
        }

        return $this;
    }

    /**
     * Add an "or where JSON length" clause to the query.
	 * 向查询中添加"or where JSON length"子句
     *
     * @param  string  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return $this
     */
    public function orWhereJsonLength($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->whereJsonLength($column, $operator, $value, 'or');
    }

    /**
     * Handles dynamic "where" clauses to the query.
	 * 处理查询的动态"where"子句
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return $this
     */
    public function dynamicWhere($method, $parameters)
    {
        $finder = substr($method, 5);

        $segments = preg_split(
            '/(And|Or)(?=[A-Z])/', $finder, -1, PREG_SPLIT_DELIM_CAPTURE
        );

        // The connector variable will determine which connector will be used for the
        // query condition. We will change it as we come across new boolean values
        // in the dynamic method strings, which could contain a number of these.
		// 连接器变量将决定将使用哪个连接器
        $connector = 'and';

        $index = 0;

        foreach ($segments as $segment) {
            // If the segment is not a boolean connector, we can assume it is a column's name
            // and we will add it to the query as a new constraint as a where clause, then
            // we can keep iterating through the dynamic method string's segments again.
            if ($segment !== 'And' && $segment !== 'Or') {
                $this->addDynamic($segment, $connector, $parameters, $index);

                $index++;
            }

            // Otherwise, we will store the connector so we know how the next where clause we
            // find in the query should be connected to the previous ones, meaning we will
            // have the proper boolean connector to connect the next where clause found.
            else {
                $connector = $segment;
            }
        }

        return $this;
    }

    /**
     * Add a single dynamic where clause statement to the query.
	 * 向查询添加一个动态where子句语句
     *
     * @param  string  $segment
     * @param  string  $connector
     * @param  array  $parameters
     * @param  int  $index
     * @return void
     */
    protected function addDynamic($segment, $connector, $parameters, $index)
    {
        // Once we have parsed out the columns and formatted the boolean operators we
        // are ready to add it to this query as a where clause just like any other
        // clause on the query. Then we'll increment the parameter index values.
        $bool = strtolower($connector);

        $this->where(Str::snake($segment), '=', $parameters[$index], $bool);
    }

    /**
     * Add a "where fulltext" clause to the query.
	 * 向查询添加"where fulltext"子句
     *
     * @param  string|string[]  $columns
     * @param  string  $value
     * @param  string  $boolean
     * @return $this
     */
    public function whereFullText($columns, $value, array $options = [], $boolean = 'and')
    {
        $type = 'Fulltext';

        $columns = (array) $columns;

        $this->wheres[] = compact('type', 'columns', 'value', 'options', 'boolean');

        $this->addBinding($value);

        return $this;
    }

    /**
     * Add a "or where fulltext" clause to the query.
	 * 向查询中添加"or where fulltext"子句
     *
     * @param  string|string[]  $columns
     * @param  string  $value
     * @return $this
     */
    public function orWhereFullText($columns, $value, array $options = [])
    {
        return $this->whereFulltext($columns, $value, $options, 'or');
    }

    /**
     * Add a "group by" clause to the query.
	 * 向查询添加"group by"子句
     *
     * @param  array|string  ...$groups
     * @return $this
     */
    public function groupBy(...$groups)
    {
        foreach ($groups as $group) {
            $this->groups = array_merge(
                (array) $this->groups,
                Arr::wrap($group)
            );
        }

        return $this;
    }

    /**
     * Add a raw groupBy clause to the query.
	 * 向查询添加一个原始groupBy子句
     *
     * @param  string  $sql
     * @param  array  $bindings
     * @return $this
     */
    public function groupByRaw($sql, array $bindings = [])
    {
        $this->groups[] = new Expression($sql);

        $this->addBinding($bindings, 'groupBy');

        return $this;
    }

    /**
     * Add a "having" clause to the query.
	 * 在查询中添加"having"子句
     *
     * @param  \Closure|string  $column
     * @param  string|int|float|null  $operator
     * @param  string|int|float|null  $value
     * @param  string  $boolean
     * @return $this
     */
    public function having($column, $operator = null, $value = null, $boolean = 'and')
    {
        $type = 'Basic';

        // Here we will make some assumptions about the operator. If only 2 values are
        // passed to the method, we will assume that the operator is an equals sign
        // and keep going. Otherwise, we'll require the operator to be passed in.
		// 这里我们将对运算符做一些假设。
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        if ($column instanceof Closure && is_null($operator)) {
            return $this->havingNested($column, $boolean);
        }

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }

        if ($this->isBitwiseOperator($operator)) {
            $type = 'Bitwise';
        }

        $this->havings[] = compact('type', 'column', 'operator', 'value', 'boolean');

        if (! $value instanceof Expression) {
            $this->addBinding($this->flattenValue($value), 'having');
        }

        return $this;
    }

    /**
     * Add an "or having" clause to the query.
	 * 在查询中添加"or having"子句
     *
     * @param  \Closure|string  $column
     * @param  string|int|float|null  $operator
     * @param  string|int|float|null  $value
     * @return $this
     */
    public function orHaving($column, $operator = null, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->having($column, $operator, $value, 'or');
    }

    /**
     * Add a nested having statement to the query.
	 * 向查询添加嵌套的having语句
     *
     * @param  \Closure  $callback
     * @param  string  $boolean
     * @return $this
     */
    public function havingNested(Closure $callback, $boolean = 'and')
    {
        $callback($query = $this->forNestedWhere());

        return $this->addNestedHavingQuery($query, $boolean);
    }

    /**
     * Add another query builder as a nested having to the query builder.
	 * 将另一个查询构建器添加为查询构建器的嵌套项
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string  $boolean
     * @return $this
     */
    public function addNestedHavingQuery($query, $boolean = 'and')
    {
        if (count($query->havings)) {
            $type = 'Nested';

            $this->havings[] = compact('type', 'query', 'boolean');

            $this->addBinding($query->getRawBindings()['having'], 'having');
        }

        return $this;
    }

    /**
     * Add a "having null" clause to the query.
	 * 向查询添加"having null"子句
     *
     * @param  string|array  $columns
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function havingNull($columns, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotNull' : 'Null';

        foreach (Arr::wrap($columns) as $column) {
            $this->havings[] = compact('type', 'column', 'boolean');
        }

        return $this;
    }

    /**
     * Add an "or having null" clause to the query.
	 * 向查询添加"or having null"子句
     *
     * @param  string  $column
     * @return $this
     */
    public function orHavingNull($column)
    {
        return $this->havingNull($column, 'or');
    }

    /**
     * Add a "having not null" clause to the query.
	 * 在查询中添加"非空"子句
     *
     * @param  string|array  $columns
     * @param  string  $boolean
     * @return $this
     */
    public function havingNotNull($columns, $boolean = 'and')
    {
        return $this->havingNull($columns, $boolean, true);
    }

    /**
     * Add an "or having not null" clause to the query.
	 * 向查询添加"or having not null"子句
     *
     * @param  string  $column
     * @return $this
     */
    public function orHavingNotNull($column)
    {
        return $this->havingNotNull($column, 'or');
    }

    /**
     * Add a "having between " clause to the query.
	 * 在查询中添加"having between"子句
     *
     * @param  string  $column
     * @param  array  $values
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function havingBetween($column, array $values, $boolean = 'and', $not = false)
    {
        $type = 'between';

        $this->havings[] = compact('type', 'column', 'values', 'boolean', 'not');

        $this->addBinding(array_slice($this->cleanBindings(Arr::flatten($values)), 0, 2), 'having');

        return $this;
    }

    /**
     * Add a raw having clause to the query.
	 * 向查询添加一个原始的having子句
     *
     * @param  string  $sql
     * @param  array  $bindings
     * @param  string  $boolean
     * @return $this
     */
    public function havingRaw($sql, array $bindings = [], $boolean = 'and')
    {
        $type = 'Raw';

        $this->havings[] = compact('type', 'sql', 'boolean');

        $this->addBinding($bindings, 'having');

        return $this;
    }

    /**
     * Add a raw or having clause to the query.
	 * 向查询添加raw或having子句
     *
     * @param  string  $sql
     * @param  array  $bindings
     * @return $this
     */
    public function orHavingRaw($sql, array $bindings = [])
    {
        return $this->havingRaw($sql, $bindings, 'or');
    }

    /**
     * Add an "order by" clause to the query.
	 * 向查询添加"order by"子句
     *
     * @param  \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Expression|string  $column
     * @param  string  $direction
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function orderBy($column, $direction = 'asc')
    {
        if ($this->isQueryable($column)) {
            [$query, $bindings] = $this->createSub($column);

            $column = new Expression('('.$query.')');

            $this->addBinding($bindings, $this->unions ? 'unionOrder' : 'order');
        }

        $direction = strtolower($direction);

        if (! in_array($direction, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('Order direction must be "asc" or "desc".');
        }

        $this->{$this->unions ? 'unionOrders' : 'orders'}[] = [
            'column' => $column,
            'direction' => $direction,
        ];

        return $this;
    }

    /**
     * Add a descending "order by" clause to the query.
	 * 向查询添加降序"order by"子句
     *
     * @param  \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Expression|string  $column
     * @return $this
     */
    public function orderByDesc($column)
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
	 * 在查询中为时间戳添加"order by"子句
     *
     * @param  \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Query\Expression|string  $column
     * @return $this
     */
    public function latest($column = 'created_at')
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
	 * 在查询中为时间戳添加"order by"子句
     *
     * @param  \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Query\Expression|string  $column
     * @return $this
     */
    public function oldest($column = 'created_at')
    {
        return $this->orderBy($column, 'asc');
    }

    /**
     * Put the query's results in random order.
	 * 将查询的结果按随机顺序排列
     *
     * @param  string|int  $seed
     * @return $this
     */
    public function inRandomOrder($seed = '')
    {
        return $this->orderByRaw($this->grammar->compileRandom($seed));
    }

    /**
     * Add a raw "order by" clause to the query.
	 * 向查询添加一个原始的"order by"子句
     *
     * @param  string  $sql
     * @param  array  $bindings
     * @return $this
     */
    public function orderByRaw($sql, $bindings = [])
    {
        $type = 'Raw';

        $this->{$this->unions ? 'unionOrders' : 'orders'}[] = compact('type', 'sql');

        $this->addBinding($bindings, $this->unions ? 'unionOrder' : 'order');

        return $this;
    }

    /**
     * Alias to set the "offset" value of the query.
	 * 设置查询的"偏移"值的别名
     *
     * @param  int  $value
     * @return $this
     */
    public function skip($value)
    {
        return $this->offset($value);
    }

    /**
     * Set the "offset" value of the query.
	 * 设置查询的"offset"值
     *
     * @param  int  $value
     * @return $this
     */
    public function offset($value)
    {
        $property = $this->unions ? 'unionOffset' : 'offset';

        $this->$property = max(0, (int) $value);

        return $this;
    }

    /**
     * Alias to set the "limit" value of the query.
	 * 设置查询的"限制"值的别名
     *
     * @param  int  $value
     * @return $this
     */
    public function take($value)
    {
        return $this->limit($value);
    }

    /**
     * Set the "limit" value of the query.
	 *设置查询的"limit"值
     *
     * @param  int  $value
     * @return $this
     */
    public function limit($value)
    {
        $property = $this->unions ? 'unionLimit' : 'limit';

        if ($value >= 0) {
            $this->$property = ! is_null($value) ? (int) $value : null;
        }

        return $this;
    }

    /**
     * Set the limit and offset for a given page.
	 * 设置给定页面的限制和偏移量
     *
     * @param  int  $page
     * @param  int  $perPage
     * @return $this
     */
    public function forPage($page, $perPage = 15)
    {
        return $this->offset(($page - 1) * $perPage)->limit($perPage);
    }

    /**
     * Constrain the query to the previous "page" of results before a given ID.
	 * 将查询约束到给定ID之前的前一个结果"页"
     *
     * @param  int  $perPage
     * @param  int|null  $lastId
     * @param  string  $column
     * @return $this
     */
    public function forPageBeforeId($perPage = 15, $lastId = 0, $column = 'id')
    {
        $this->orders = $this->removeExistingOrdersFor($column);

        if (! is_null($lastId)) {
            $this->where($column, '<', $lastId);
        }

        return $this->orderBy($column, 'desc')
                    ->limit($perPage);
    }

    /**
     * Constrain the query to the next "page" of results after a given ID.
	 * 将查询约束到给定ID之后的下一个结果"页面"
     *
     * @param  int  $perPage
     * @param  int|null  $lastId
     * @param  string  $column
     * @return $this
     */
    public function forPageAfterId($perPage = 15, $lastId = 0, $column = 'id')
    {
        $this->orders = $this->removeExistingOrdersFor($column);

        if (! is_null($lastId)) {
            $this->where($column, '>', $lastId);
        }

        return $this->orderBy($column, 'asc')
                    ->limit($perPage);
    }

    /**
     * Remove all existing orders and optionally add a new order.
	 * 删除所有现有订单，并可选择添加新订单。
     *
     * @param  \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Query\Expression|string|null  $column
     * @param  string  $direction
     * @return $this
     */
    public function reorder($column = null, $direction = 'asc')
    {
        $this->orders = null;
        $this->unionOrders = null;
        $this->bindings['order'] = [];
        $this->bindings['unionOrder'] = [];

        if ($column) {
            return $this->orderBy($column, $direction);
        }

        return $this;
    }

    /**
     * Get an array with all orders with a given column removed.
	 * 获取一个数组，其中包含删除给定列的所有订单。
     *
     * @param  string  $column
     * @return array
     */
    protected function removeExistingOrdersFor($column)
    {
        return Collection::make($this->orders)
                    ->reject(function ($order) use ($column) {
                        return isset($order['column'])
                               ? $order['column'] === $column : false;
                    })->values()->all();
    }

    /**
     * Add a union statement to the query.
	 * 向查询添加一个联合语句
     *
     * @param  \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $query
     * @param  bool  $all
     * @return $this
     */
    public function union($query, $all = false)
    {
        if ($query instanceof Closure) {
            $query($query = $this->newQuery());
        }

        $this->unions[] = compact('query', 'all');

        $this->addBinding($query->getBindings(), 'union');

        return $this;
    }

    /**
     * Add a union all statement to the query.
	 * 向查询添加一个联合all语句
     *
     * @param  \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $query
     * @return $this
     */
    public function unionAll($query)
    {
        return $this->union($query, true);
    }

    /**
     * Lock the selected rows in the table.
	 * 锁定表中选定的行
     *
     * @param  string|bool  $value
     * @return $this
     */
    public function lock($value = true)
    {
        $this->lock = $value;

        if (! is_null($this->lock)) {
            $this->useWritePdo();
        }

        return $this;
    }

    /**
     * Lock the selected rows in the table for updating.
	 * 锁定表中所选的行以进行更新
     *
     * @return $this
     */
    public function lockForUpdate()
    {
        return $this->lock(true);
    }

    /**
     * Share lock the selected rows in the table.
	 * 共享锁表中选定的行
     *
     * @return $this
     */
    public function sharedLock()
    {
        return $this->lock(false);
    }

    /**
     * Register a closure to be invoked before the query is executed.
	 * 在执行查询之前注册要调用的闭包
     *
     * @param  callable  $callback
     * @return $this
     */
    public function beforeQuery(callable $callback)
    {
        $this->beforeQueryCallbacks[] = $callback;

        return $this;
    }

    /**
     * Invoke the "before query" modification callbacks.
	 * 调用"before query"修改回调
     *
     * @return void
     */
    public function applyBeforeQueryCallbacks()
    {
        foreach ($this->beforeQueryCallbacks as $callback) {
            $callback($this);
        }

        $this->beforeQueryCallbacks = [];
    }

    /**
     * Get the SQL representation of the query.
	 * 获取查询的SQL表示形式
     *
     * @return string
     */
    public function toSql()
    {
        $this->applyBeforeQueryCallbacks();

        return $this->grammar->compileSelect($this);
    }

    /**
     * Execute a query for a single record by ID.
	 * 按ID对单个记录执行查询
     *
     * @param  int|string  $id
     * @param  array|string  $columns
     * @return mixed|static
     */
    public function find($id, $columns = ['*'])
    {
        return $this->where('id', '=', $id)->first($columns);
    }

    /**
     * Execute a query for a single record by ID or call a callback.
	 * 按ID对单个记录执行查询或调用回调
     *
     * @param  mixed  $id
     * @param  \Closure|array|string  $columns
     * @param  \Closure|null  $callback
     * @return mixed|static
     */
    public function findOr($id, $columns = ['*'], Closure $callback = null)
    {
        if ($columns instanceof Closure) {
            $callback = $columns;

            $columns = ['*'];
        }

        if (! is_null($data = $this->find($id, $columns))) {
            return $data;
        }

        return $callback();
    }

    /**
     * Get a single column's value from the first result of a query.
	 * 从查询的第一个结果中获取单个列的值
     *
     * @param  string  $column
     * @return mixed
     */
    public function value($column)
    {
        $result = (array) $this->first([$column]);

        return count($result) > 0 ? reset($result) : null;
    }

    /**
     * Get a single expression value from the first result of a query.
	 * 从查询的第一个结果中获取单个表达式值
     *
     * @param  string  $expression
     * @param  array  $bindings
     * @return mixed
     */
    public function rawValue(string $expression, array $bindings = [])
    {
        $result = (array) $this->selectRaw($expression, $bindings)->first();

        return count($result) > 0 ? reset($result) : null;
    }

    /**
     * Get a single column's value from the first result of a query if it's the sole matching record.
	 * 如果查询的第一个结果是唯一匹配的记录，则从该结果中获取单个列的值。
     *
     * @param  string  $column
     * @return mixed
     *
     * @throws \Illuminate\Database\RecordsNotFoundException
     * @throws \Illuminate\Database\MultipleRecordsFoundException
     */
    public function soleValue($column)
    {
        $result = (array) $this->sole([$column]);

        return reset($result);
    }

    /**
     * Execute the query as a "select" statement.
	 * 以"select"语句的形式执行查询
     *
     * @param  array|string  $columns
     * @return \Illuminate\Support\Collection
     */
    public function get($columns = ['*'])
    {
        return collect($this->onceWithColumns(Arr::wrap($columns), function () {
            return $this->processor->processSelect($this, $this->runSelect());
        }));
    }

    /**
     * Run the query as a "select" statement against the connection.
	 * 对连接以"select"语句的形式运行查询
     *
     * @return array
     */
    protected function runSelect()
    {
        return $this->connection->select(
            $this->toSql(), $this->getBindings(), ! $this->useWritePdo
        );
    }

    /**
     * Paginate the given query into a simple paginator.
	 * 将给定查询分页到一个简单的分页器中
     *
     * @param  int|\Closure  $perPage
     * @param  array|string  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $total = $this->getCountForPagination();

        $perPage = $perPage instanceof Closure ? $perPage($total) : $perPage;

        $results = $total ? $this->forPage($page, $perPage)->get($columns) : collect();

        return $this->paginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * Get a paginator only supporting simple next and previous links.
	 * 获取只支持简单的下一个和上一个链接的分页器
     *
     * This is more efficient on larger data-sets, etc.
	 * 这在更大的数据集上更有效
     *
     * @param  int  $perPage
     * @param  array|string  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function simplePaginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $this->offset(($page - 1) * $perPage)->limit($perPage + 1);

        return $this->simplePaginator($this->get($columns), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * Get a paginator only supporting simple next and previous links.
	 * 获取只支持简单的下一个和上一个链接的分页器
     *
     * This is more efficient on larger data-sets, etc.
     *
     * @param  int|null  $perPage
     * @param  array|string  $columns
     * @param  string  $cursorName
     * @param  \Illuminate\Pagination\Cursor|string|null  $cursor
     * @return \Illuminate\Contracts\Pagination\CursorPaginator
     */
    public function cursorPaginate($perPage = 15, $columns = ['*'], $cursorName = 'cursor', $cursor = null)
    {
        return $this->paginateUsingCursor($perPage, $columns, $cursorName, $cursor);
    }

    /**
     * Ensure the proper order by required for cursor pagination.
	 * 确保游标分页所需的正确顺序
     *
     * @param  bool  $shouldReverse
     * @return \Illuminate\Support\Collection
     */
    protected function ensureOrderForCursorPagination($shouldReverse = false)
    {
        $this->enforceOrderBy();

        return collect($this->orders ?? $this->unionOrders ?? [])->filter(function ($order) {
            return Arr::has($order, 'direction');
        })->when($shouldReverse, function (Collection $orders) {
            return $orders->map(function ($order) {
                $order['direction'] = $order['direction'] === 'asc' ? 'desc' : 'asc';

                return $order;
            });
        })->values();
    }

    /**
     * Get the count of the total records for the paginator.
	 * 获取分页器的总记录计数
     *
     * @param  array  $columns
     * @return int
     */
    public function getCountForPagination($columns = ['*'])
    {
        $results = $this->runPaginationCountQuery($columns);

        // Once we have run the pagination count query, we will get the resulting count and
        // take into account what type of query it was. When there is a group by we will
        // just return the count of the entire results set since that will be correct.
		// 运行分页计数查询后，我们将获得结果计数并考虑它是什么类型的查询。
        if (! isset($results[0])) {
            return 0;
        } elseif (is_object($results[0])) {
            return (int) $results[0]->aggregate;
        }

        return (int) array_change_key_case((array) $results[0])['aggregate'];
    }

    /**
     * Run a pagination count query.
	 * 运行分页计数查询
     *
     * @param  array  $columns
     * @return array
     */
    protected function runPaginationCountQuery($columns = ['*'])
    {
        if ($this->groups || $this->havings) {
            $clone = $this->cloneForPaginationCount();

            if (is_null($clone->columns) && ! empty($this->joins)) {
                $clone->select($this->from.'.*');
            }

            return $this->newQuery()
                ->from(new Expression('('.$clone->toSql().') as '.$this->grammar->wrap('aggregate_table')))
                ->mergeBindings($clone)
                ->setAggregate('count', $this->withoutSelectAliases($columns))
                ->get()->all();
        }

        $without = $this->unions ? ['orders', 'limit', 'offset'] : ['columns', 'orders', 'limit', 'offset'];

        return $this->cloneWithout($without)
                    ->cloneWithoutBindings($this->unions ? ['order'] : ['select', 'order'])
                    ->setAggregate('count', $this->withoutSelectAliases($columns))
                    ->get()->all();
    }

    /**
     * Clone the existing query instance for usage in a pagination subquery.
	 * 克隆现有查询实例，以便在分页子查询中使用。
     *
     * @return self
     */
    protected function cloneForPaginationCount()
    {
        return $this->cloneWithout(['orders', 'limit', 'offset'])
                    ->cloneWithoutBindings(['order']);
    }

    /**
     * Remove the column aliases since they will break count queries.
	 * 删除列别名，因为它们会中断计数查询。
     *
     * @param  array  $columns
     * @return array
     */
    protected function withoutSelectAliases(array $columns)
    {
        return array_map(function ($column) {
            return is_string($column) && ($aliasPosition = stripos($column, ' as ')) !== false
                    ? substr($column, 0, $aliasPosition) : $column;
        }, $columns);
    }

    /**
     * Get a lazy collection for the given query.
	 * 获取给定查询的惰性集合
     *
     * @return \Illuminate\Support\LazyCollection
     */
    public function cursor()
    {
        if (is_null($this->columns)) {
            $this->columns = ['*'];
        }

        return new LazyCollection(function () {
            yield from $this->connection->cursor(
                $this->toSql(), $this->getBindings(), ! $this->useWritePdo
            );
        });
    }

    /**
     * Throw an exception if the query doesn't have an orderBy clause.
	 * 如果查询没有orderBy子句，则抛出异常。
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    protected function enforceOrderBy()
    {
        if (empty($this->orders) && empty($this->unionOrders)) {
            throw new RuntimeException('You must specify an orderBy clause when using this function.');
        }
    }

    /**
     * Get a collection instance containing the values of a given column.
	 * 获取包含给定列值的集合实例
     *
     * @param  string  $column
     * @param  string|null  $key
     * @return \Illuminate\Support\Collection
     */
    public function pluck($column, $key = null)
    {
        // First, we will need to select the results of the query accounting for the
        // given columns / key. Once we have the results, we will be able to take
        // the results and get the exact data that was requested for the query.
		// 首先，我们需要选择查询的结果。
        $queryResult = $this->onceWithColumns(
            is_null($key) ? [$column] : [$column, $key],
            function () {
                return $this->processor->processSelect(
                    $this, $this->runSelect()
                );
            }
        );

        if (empty($queryResult)) {
            return collect();
        }

        // If the columns are qualified with a table or have an alias, we cannot use
        // those directly in the "pluck" operations since the results from the DB
        // are only keyed by the column itself. We'll strip the table out here.
		// 如果列用表限定或具有别名，则不能使用。
        $column = $this->stripTableForPluck($column);

        $key = $this->stripTableForPluck($key);

        return is_array($queryResult[0])
                    ? $this->pluckFromArrayColumn($queryResult, $column, $key)
                    : $this->pluckFromObjectColumn($queryResult, $column, $key);
    }

    /**
     * Strip off the table name or alias from a column identifier.
	 * 从列标识符中去掉表名或别名
     *
     * @param  string  $column
     * @return string|null
     */
    protected function stripTableForPluck($column)
    {
        if (is_null($column)) {
            return $column;
        }

        $separator = str_contains(strtolower($column), ' as ') ? ' as ' : '\.';

        return last(preg_split('~'.$separator.'~i', $column));
    }

    /**
     * Retrieve column values from rows represented as objects.
	 * 从表示为对象的行中检索列值
     *
     * @param  array  $queryResult
     * @param  string  $column
     * @param  string  $key
     * @return \Illuminate\Support\Collection
     */
    protected function pluckFromObjectColumn($queryResult, $column, $key)
    {
        $results = [];

        if (is_null($key)) {
            foreach ($queryResult as $row) {
                $results[] = $row->$column;
            }
        } else {
            foreach ($queryResult as $row) {
                $results[$row->$key] = $row->$column;
            }
        }

        return collect($results);
    }

    /**
     * Retrieve column values from rows represented as arrays.
	 * 从表示为数组的行中检索列值
     *
     * @param  array  $queryResult
     * @param  string  $column
     * @param  string  $key
     * @return \Illuminate\Support\Collection
     */
    protected function pluckFromArrayColumn($queryResult, $column, $key)
    {
        $results = [];

        if (is_null($key)) {
            foreach ($queryResult as $row) {
                $results[] = $row[$column];
            }
        } else {
            foreach ($queryResult as $row) {
                $results[$row[$key]] = $row[$column];
            }
        }

        return collect($results);
    }

    /**
     * Concatenate values of a given column as a string.
	 * 将给定列的值连接为字符串
     *
     * @param  string  $column
     * @param  string  $glue
     * @return string
     */
    public function implode($column, $glue = '')
    {
        return $this->pluck($column)->implode($glue);
    }

    /**
     * Determine if any rows exist for the current query.
	 * 确定当前查询是否存在任何行
     *
     * @return bool
     */
    public function exists()
    {
        $this->applyBeforeQueryCallbacks();

        $results = $this->connection->select(
            $this->grammar->compileExists($this), $this->getBindings(), ! $this->useWritePdo
        );

        // If the results have rows, we will get the row and see if the exists column is a
        // boolean true. If there are no results for this query we will return false as
        // there are no rows for this query at all, and we can return that info here.
		// 如果结果有行，我们将获取行并查看存在列是否为真。
        if (isset($results[0])) {
            $results = (array) $results[0];

            return (bool) $results['exists'];
        }

        return false;
    }

    /**
     * Determine if no rows exist for the current query.
	 * 确定当前查询是否不存在行
     *
     * @return bool
     */
    public function doesntExist()
    {
        return ! $this->exists();
    }

    /**
     * Execute the given callback if no rows exist for the current query.
	 * 如果当前查询不存在行，则执行给定的回调。
     *
     * @param  \Closure  $callback
     * @return mixed
     */
    public function existsOr(Closure $callback)
    {
        return $this->exists() ? true : $callback();
    }

    /**
     * Execute the given callback if rows exist for the current query.
	 * 如果当前查询存在行，则执行给定的回调。
     *
     * @param  \Closure  $callback
     * @return mixed
     */
    public function doesntExistOr(Closure $callback)
    {
        return $this->doesntExist() ? true : $callback();
    }

    /**
     * Retrieve the "count" result of the query.
	 * 检索查询的"count"结果
     *
     * @param  string  $columns
     * @return int
     */
    public function count($columns = '*')
    {
        return (int) $this->aggregate(__FUNCTION__, Arr::wrap($columns));
    }

    /**
     * Retrieve the minimum value of a given column.
	 * 检索给定列的最小值
     *
     * @param  string  $column
     * @return mixed
     */
    public function min($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }

    /**
     * Retrieve the maximum value of a given column.
	 * 检索给定列的最大值
     *
     * @param  string  $column
     * @return mixed
     */
    public function max($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }

    /**
     * Retrieve the sum of the values of a given column.
	 * 检索给定列的值之和
     *
     * @param  string  $column
     * @return mixed
     */
    public function sum($column)
    {
        $result = $this->aggregate(__FUNCTION__, [$column]);

        return $result ?: 0;
    }

    /**
     * Retrieve the average of the values of a given column.
	 * 检索给定列的值的平均值
     *
     * @param  string  $column
     * @return mixed
     */
    public function avg($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }

    /**
     * Alias for the "avg" method.
	 * "avg"方法的别名
     *
     * @param  string  $column
     * @return mixed
     */
    public function average($column)
    {
        return $this->avg($column);
    }

    /**
     * Execute an aggregate function on the database.
	 * 在数据库上执行聚合函数
     *
     * @param  string  $function
     * @param  array  $columns
     * @return mixed
     */
    public function aggregate($function, $columns = ['*'])
    {
        $results = $this->cloneWithout($this->unions || $this->havings ? [] : ['columns'])
                        ->cloneWithoutBindings($this->unions || $this->havings ? [] : ['select'])
                        ->setAggregate($function, $columns)
                        ->get($columns);

        if (! $results->isEmpty()) {
            return array_change_key_case((array) $results[0])['aggregate'];
        }
    }

    /**
     * Execute a numeric aggregate function on the database.
	 * 在数据库上执行一个数字聚合函数
     *
     * @param  string  $function
     * @param  array  $columns
     * @return float|int
     */
    public function numericAggregate($function, $columns = ['*'])
    {
        $result = $this->aggregate($function, $columns);

        // If there is no result, we can obviously just return 0 here. Next, we will check
        // if the result is an integer or float. If it is already one of these two data
        // types we can just return the result as-is, otherwise we will convert this.
		// 如果没有结果，我们显然可以在这里返回0。
        if (! $result) {
            return 0;
        }

        if (is_int($result) || is_float($result)) {
            return $result;
        }

        // If the result doesn't contain a decimal place, we will assume it is an int then
        // cast it to one. When it does we will cast it to a float since it needs to be
        // cast to the expected data type for the developers out of pure convenience.
        return ! str_contains((string) $result, '.')
                ? (int) $result : (float) $result;
    }

    /**
     * Set the aggregate property without running the query.
	 * 设置聚合属性而不运行查询
     *
     * @param  string  $function
     * @param  array  $columns
     * @return $this
     */
    protected function setAggregate($function, $columns)
    {
        $this->aggregate = compact('function', 'columns');

        if (empty($this->groups)) {
            $this->orders = null;

            $this->bindings['order'] = [];
        }

        return $this;
    }

    /**
     * Execute the given callback while selecting the given columns.
	 * 在选择给定列时执行给定的回调
     *
     * After running the callback, the columns are reset to the original value.
     *
     * @param  array  $columns
     * @param  callable  $callback
     * @return mixed
     */
    protected function onceWithColumns($columns, $callback)
    {
        $original = $this->columns;

        if (is_null($original)) {
            $this->columns = $columns;
        }

        $result = $callback();

        $this->columns = $original;

        return $result;
    }

    /**
     * Insert new records into the database.
	 * 向数据库中插入新记录
     *
     * @param  array  $values
     * @return bool
     */
    public function insert(array $values)
    {
        // Since every insert gets treated like a batch insert, we will make sure the
        // bindings are structured in a way that is convenient when building these
        // inserts statements by verifying these elements are actually an array.
		// 由于每次插入都被视为批处理插入，因此我们将确保。
        if (empty($values)) {
            return true;
        }

        if (! is_array(reset($values))) {
            $values = [$values];
        }

        // Here, we will sort the insert keys for every record so that each insert is
        // in the same order for the record. We need to make sure this is the case
        // so there are not any errors or problems when inserting these records.
        else {
            foreach ($values as $key => $value) {
                ksort($value);

                $values[$key] = $value;
            }
        }

        $this->applyBeforeQueryCallbacks();

        // Finally, we will run this query against the database connection and return
        // the results. We will need to also flatten these bindings before running
        // the query so they are all in one huge, flattened array for execution.
        return $this->connection->insert(
            $this->grammar->compileInsert($this, $values),
            $this->cleanBindings(Arr::flatten($values, 1))
        );
    }

    /**
     * Insert new records into the database while ignoring errors.
	 * 将新记录插入数据库，同时忽略错误。
     *
     * @param  array  $values
     * @return int
     */
    public function insertOrIgnore(array $values)
    {
        if (empty($values)) {
            return 0;
        }

        if (! is_array(reset($values))) {
            $values = [$values];
        } else {
            foreach ($values as $key => $value) {
                ksort($value);
                $values[$key] = $value;
            }
        }

        $this->applyBeforeQueryCallbacks();

        return $this->connection->affectingStatement(
            $this->grammar->compileInsertOrIgnore($this, $values),
            $this->cleanBindings(Arr::flatten($values, 1))
        );
    }

    /**
     * Insert a new record and get the value of the primary key.
	 * 插入一条新记录并获取主键的值
     *
     * @param  array  $values
     * @param  string|null  $sequence
     * @return int
     */
    public function insertGetId(array $values, $sequence = null)
    {
        $this->applyBeforeQueryCallbacks();

        $sql = $this->grammar->compileInsertGetId($this, $values, $sequence);

        $values = $this->cleanBindings($values);

        return $this->processor->processInsertGetId($this, $sql, $values, $sequence);
    }

    /**
     * Insert new records into the table using a subquery.
	 * 使用子查询将新记录插入表中
     *
     * @param  array  $columns
     * @param  \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|string  $query
     * @return int
     */
    public function insertUsing(array $columns, $query)
    {
        $this->applyBeforeQueryCallbacks();

        [$sql, $bindings] = $this->createSub($query);

        return $this->connection->affectingStatement(
            $this->grammar->compileInsertUsing($this, $columns, $sql),
            $this->cleanBindings($bindings)
        );
    }

    /**
     * Update records in the database.
	 * 更新数据库中的记录
     *
     * @param  array  $values
     * @return int
     */
    public function update(array $values)
    {
        $this->applyBeforeQueryCallbacks();

        $sql = $this->grammar->compileUpdate($this, $values);

        return $this->connection->update($sql, $this->cleanBindings(
            $this->grammar->prepareBindingsForUpdate($this->bindings, $values)
        ));
    }

    /**
     * Update records in a PostgreSQL database using the update from syntax.
	 * 使用Update from语法更新PostgreSQL数据库中的记录
     *
     * @param  array  $values
     * @return int
     */
    public function updateFrom(array $values)
    {
        if (! method_exists($this->grammar, 'compileUpdateFrom')) {
            throw new LogicException('This database engine does not support the updateFrom method.');
        }

        $this->applyBeforeQueryCallbacks();

        $sql = $this->grammar->compileUpdateFrom($this, $values);

        return $this->connection->update($sql, $this->cleanBindings(
            $this->grammar->prepareBindingsForUpdateFrom($this->bindings, $values)
        ));
    }

    /**
     * Insert or update a record matching the attributes, and fill it with values.
	 * 插入或更新与属性匹配的记录，并用值填充它。
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return bool
     */
    public function updateOrInsert(array $attributes, array $values = [])
    {
        if (! $this->where($attributes)->exists()) {
            return $this->insert(array_merge($attributes, $values));
        }

        if (empty($values)) {
            return true;
        }

        return (bool) $this->limit(1)->update($values);
    }

    /**
     * Insert new records or update the existing ones.
	 * 插入新记录或更新现有记录
     *
     * @param  array  $values
     * @param  array|string  $uniqueBy
     * @param  array|null  $update
     * @return int
     */
    public function upsert(array $values, $uniqueBy, $update = null)
    {
        if (empty($values)) {
            return 0;
        } elseif ($update === []) {
            return (int) $this->insert($values);
        }

        if (! is_array(reset($values))) {
            $values = [$values];
        } else {
            foreach ($values as $key => $value) {
                ksort($value);

                $values[$key] = $value;
            }
        }

        if (is_null($update)) {
            $update = array_keys(reset($values));
        }

        $this->applyBeforeQueryCallbacks();

        $bindings = $this->cleanBindings(array_merge(
            Arr::flatten($values, 1),
            collect($update)->reject(function ($value, $key) {
                return is_int($key);
            })->all()
        ));

        return $this->connection->affectingStatement(
            $this->grammar->compileUpsert($this, $values, (array) $uniqueBy, $update),
            $bindings
        );
    }

    /**
     * Increment a column's value by a given amount.
	 * 将列的值增加给定的量
     *
     * @param  string  $column
     * @param  float|int  $amount
     * @param  array  $extra
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    public function increment($column, $amount = 1, array $extra = [])
    {
        if (! is_numeric($amount)) {
            throw new InvalidArgumentException('Non-numeric value passed to increment method.');
        }

        return $this->incrementEach([$column => $amount], $extra);
    }

    /**
     * Increment the given column's values by the given amounts.
	 * 将给定列的值增加给定的量
     *
     * @param  array<string, float|int|numeric-string>  $columns
     * @param  array<string, mixed>  $extra
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    public function incrementEach(array $columns, array $extra = [])
    {
        foreach ($columns as $column => $amount) {
            if (! is_numeric($amount)) {
                throw new InvalidArgumentException("Non-numeric value passed as increment amount for column: '$column'.");
            } elseif (! is_string($column)) {
                throw new InvalidArgumentException('Non-associative array passed to incrementEach method.');
            }

            $columns[$column] = $this->raw("{$this->grammar->wrap($column)} + $amount");
        }

        return $this->update(array_merge($columns, $extra));
    }

    /**
     * Decrement a column's value by a given amount.
	 * 将列的值递减给定的量
     *
     * @param  string  $column
     * @param  float|int  $amount
     * @param  array  $extra
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    public function decrement($column, $amount = 1, array $extra = [])
    {
        if (! is_numeric($amount)) {
            throw new InvalidArgumentException('Non-numeric value passed to decrement method.');
        }

        return $this->decrementEach([$column => $amount], $extra);
    }

    /**
     * Decrement the given column's values by the given amounts.
	 * 将给定列的值减去给定的量
     *
     * @param  array<string, float|int|numeric-string>  $columns
     * @param  array<string, mixed>  $extra
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    public function decrementEach(array $columns, array $extra = [])
    {
        foreach ($columns as $column => $amount) {
            if (! is_numeric($amount)) {
                throw new InvalidArgumentException("Non-numeric value passed as decrement amount for column: '$column'.");
            } elseif (! is_string($column)) {
                throw new InvalidArgumentException('Non-associative array passed to decrementEach method.');
            }

            $columns[$column] = $this->raw("{$this->grammar->wrap($column)} - $amount");
        }

        return $this->update(array_merge($columns, $extra));
    }

    /**
     * Delete records from the database.
	 * 删除数据库中的记录
     *
     * @param  mixed  $id
     * @return int
     */
    public function delete($id = null)
    {
        // If an ID is passed to the method, we will set the where clause to check the
        // ID to let developers to simply and quickly remove a single row from this
        // database without manually specifying the "where" clauses on the query.
		// 如果将ID传递给方法，我们将设置where子句来检查。
        if (! is_null($id)) {
            $this->where($this->from.'.id', '=', $id);
        }

        $this->applyBeforeQueryCallbacks();

        return $this->connection->delete(
            $this->grammar->compileDelete($this), $this->cleanBindings(
                $this->grammar->prepareBindingsForDelete($this->bindings)
            )
        );
    }

    /**
     * Run a truncate statement on the table.
	 * 在表上运行truncate语句
     *
     * @return void
     */
    public function truncate()
    {
        $this->applyBeforeQueryCallbacks();

        foreach ($this->grammar->compileTruncate($this) as $sql => $bindings) {
            $this->connection->statement($sql, $bindings);
        }
    }

    /**
     * Get a new instance of the query builder.
	 * 获取查询生成器的新实例
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function newQuery()
    {
        return new static($this->connection, $this->grammar, $this->processor);
    }

    /**
     * Create a new query instance for a sub-query.
	 * 为子查询创建一个新的查询实例
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function forSubQuery()
    {
        return $this->newQuery();
    }

    /**
     * Create a raw database expression.
	 * 创建原始数据库表达式
     *
     * @param  mixed  $value
     * @return \Illuminate\Database\Query\Expression
     */
    public function raw($value)
    {
        return $this->connection->raw($value);
    }

    /**
     * Get the current query value bindings in a flattened array.
	 * 在扁平数组中获取当前查询值绑定
     *
     * @return array
     */
    public function getBindings()
    {
        return Arr::flatten($this->bindings);
    }

    /**
     * Get the raw array of bindings.
	 * 获取绑定的原始数组
     *
     * @return array
     */
    public function getRawBindings()
    {
        return $this->bindings;
    }

    /**
     * Set the bindings on the query builder.
	 * 在查询生成器上设置绑定
     *
     * @param  array  $bindings
     * @param  string  $type
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setBindings(array $bindings, $type = 'where')
    {
        if (! array_key_exists($type, $this->bindings)) {
            throw new InvalidArgumentException("Invalid binding type: {$type}.");
        }

        $this->bindings[$type] = $bindings;

        return $this;
    }

    /**
     * Add a binding to the query.
	 * 向查询添加绑定
     *
     * @param  mixed  $value
     * @param  string  $type
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function addBinding($value, $type = 'where')
    {
        if (! array_key_exists($type, $this->bindings)) {
            throw new InvalidArgumentException("Invalid binding type: {$type}.");
        }

        if (is_array($value)) {
            $this->bindings[$type] = array_values(array_map(
                [$this, 'castBinding'],
                array_merge($this->bindings[$type], $value),
            ));
        } else {
            $this->bindings[$type][] = $this->castBinding($value);
        }

        return $this;
    }

    /**
     * Cast the given binding value.
	 * 强制转换给定的绑定值
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function castBinding($value)
    {
        if (function_exists('enum_exists') && $value instanceof BackedEnum) {
            return $value->value;
        }

        return $value;
    }

    /**
     * Merge an array of bindings into our bindings.
	 * 将绑定数组合并到我们的绑定中
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return $this
     */
    public function mergeBindings(self $query)
    {
        $this->bindings = array_merge_recursive($this->bindings, $query->bindings);

        return $this;
    }

    /**
     * Remove all of the expressions from a list of bindings.
	 * 从绑定列表中删除所有表达式
     *
     * @param  array  $bindings
     * @return array
     */
    public function cleanBindings(array $bindings)
    {
        return collect($bindings)
                    ->reject(function ($binding) {
                        return $binding instanceof Expression;
                    })
                    ->map([$this, 'castBinding'])
                    ->values()
                    ->all();
    }

    /**
     * Get a scalar type value from an unknown type of input.
	 * 从未知类型的输入获取标量类型值
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function flattenValue($value)
    {
        return is_array($value) ? head(Arr::flatten($value)) : $value;
    }

    /**
     * Get the default key name of the table.
	 * 获取表的默认键名
     *
     * @return string
     */
    protected function defaultKeyName()
    {
        return 'id';
    }

    /**
     * Get the database connection instance.
	 * 获取数据库连接实例
     *
     * @return \Illuminate\Database\ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get the database query processor instance.
	 * 获取数据库查询处理器实例
     *
     * @return \Illuminate\Database\Query\Processors\Processor
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * Get the query grammar instance.
	 * 获取查询语法实例
     *
     * @return \Illuminate\Database\Query\Grammars\Grammar
     */
    public function getGrammar()
    {
        return $this->grammar;
    }

    /**
     * Use the "write" PDO connection when executing the query.
	 * 在执行查询时使用"write"PDO连接
     *
     * @return $this
     */
    public function useWritePdo()
    {
        $this->useWritePdo = true;

        return $this;
    }

    /**
     * Determine if the value is a query builder instance or a Closure.
	 * 确定该值是查询生成器实例还是Closure
     *
     * @param  mixed  $value
     * @return bool
     */
    protected function isQueryable($value)
    {
        return $value instanceof self ||
               $value instanceof EloquentBuilder ||
               $value instanceof Relation ||
               $value instanceof Closure;
    }

    /**
     * Clone the query.
	 * 克隆查询
     *
     * @return static
     */
    public function clone()
    {
        return clone $this;
    }

    /**
     * Clone the query without the given properties.
	 * 克隆不含给定属性的查询
     *
     * @param  array  $properties
     * @return static
     */
    public function cloneWithout(array $properties)
    {
        return tap($this->clone(), function ($clone) use ($properties) {
            foreach ($properties as $property) {
                $clone->{$property} = null;
            }
        });
    }

    /**
     * Clone the query without the given bindings.
	 * 克隆没有给定绑定的查询
     *
     * @param  array  $except
     * @return static
     */
    public function cloneWithoutBindings(array $except)
    {
        return tap($this->clone(), function ($clone) use ($except) {
            foreach ($except as $type) {
                $clone->bindings[$type] = [];
            }
        });
    }

    /**
     * Dump the current SQL and bindings.
	 * 转储当前SQL和绑定
     *
     * @return $this
     */
    public function dump()
    {
        dump($this->toSql(), $this->getBindings());

        return $this;
    }

    /**
     * Die and dump the current SQL and bindings.
	 * 终止并转储当前SQL和绑定
     *
     * @return never
     */
    public function dd()
    {
        dd($this->toSql(), $this->getBindings());
    }

    /**
     * Handle dynamic method calls into the method.
	 * 处理对方法的动态方法调用
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if (str_starts_with($method, 'where')) {
            return $this->dynamicWhere($method, $parameters);
        }

        static::throwBadMethodCallException($method);
    }
}
