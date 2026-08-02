<?php
/**
 * Illuminate，数据库，查询，语法，Sql Server 语法
 */

namespace Illuminate\Database\Query\Grammars;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SqlServerGrammar extends Grammar
{
    /**
     * All of the available clause operators.
	 * 所有可用的子句操作符
     *
     * @var string[]
     */
    protected $operators = [
        '=', '<', '>', '<=', '>=', '!<', '!>', '<>', '!=',
        'like', 'not like', 'ilike',
        '&', '&=', '|', '|=', '^', '^=',
    ];

    /**
     * Compile a select query into SQL.
	 * 将一个选择查询编译成SQL
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return string
     */
    public function compileSelect(Builder $query)
    {
        if (! $query->offset) {
            return parent::compileSelect($query);
        }

        if (is_null($query->columns)) {
            $query->columns = ['*'];
        }

        $components = $this->compileComponents($query);

        if (! empty($components['orders'])) {
            return parent::compileSelect($query)." offset {$query->offset} rows fetch next {$query->limit} rows only";
        }

        // If an offset is present on the query, we will need to wrap the query in
        // a big "ANSI" offset syntax block. This is very nasty compared to the
        // other database systems but is necessary for implementing features.
		// 如果查询中存在偏移量，则需要将查询封装在一个大的"ANSI"偏移语法块。
        return $this->compileAnsiOffset(
            $query, $components
        );
    }

    /**
     * Compile the "select *" portion of the query.
	 * 编译查询的"select *"部分
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $columns
     * @return string|null
     */
    protected function compileColumns(Builder $query, $columns)
    {
        if (! is_null($query->aggregate)) {
            return;
        }

        $select = $query->distinct ? 'select distinct ' : 'select ';

        // If there is a limit on the query, but not an offset, we will add the top
        // clause to the query, which serves as a "limit" type clause within the
        // SQL Server system similar to the limit keywords available in MySQL.
		// 如果查询上有限制，但没有偏移量，我们将添加顶部子句到查询。
        if (is_numeric($query->limit) && $query->limit > 0 && $query->offset <= 0) {
            $select .= 'top '.((int) $query->limit).' ';
        }

        return $select.$this->columnize($columns);
    }

    /**
     * Compile the "from" portion of the query.
	 * 编译查询的"from"部分
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string  $table
     * @return string
     */
    protected function compileFrom(Builder $query, $table)
    {
        $from = parent::compileFrom($query, $table);

        if (is_string($query->lock)) {
            return $from.' '.$query->lock;
        }

        if (! is_null($query->lock)) {
            return $from.' with(rowlock,'.($query->lock ? 'updlock,' : '').'holdlock)';
        }

        return $from;
    }

    /**
     * {@inheritdoc}
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereBitwise(Builder $query, $where)
    {
        $value = $this->parameter($where['value']);

        $operator = str_replace('?', '??', $where['operator']);

        return '('.$this->wrap($where['column']).' '.$operator.' '.$value.') != 0';
    }

    /**
     * Compile a "where date" clause.
	 * 编译一个"where date"子句
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereDate(Builder $query, $where)
    {
        $value = $this->parameter($where['value']);

        return 'cast('.$this->wrap($where['column']).' as date) '.$where['operator'].' '.$value;
    }

    /**
     * Compile a "where time" clause.
	 * 编写一个"where time"子句
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereTime(Builder $query, $where)
    {
        $value = $this->parameter($where['value']);

        return 'cast('.$this->wrap($where['column']).' as time) '.$where['operator'].' '.$value;
    }

    /**
     * Compile a "JSON contains" statement into SQL.
	 * 将"JSON contains"语句编译成SQL
     *
     * @param  string  $column
     * @param  string  $value
     * @return string
     */
    protected function compileJsonContains($column, $value)
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($column);

        return $value.' in (select [value] from openjson('.$field.$path.'))';
    }

    /**
     * Prepare the binding for a "JSON contains" statement.
	 * 为"JSON contains"语句准备绑定
     *
     * @param  mixed  $binding
     * @return string
     */
    public function prepareBindingForJsonContains($binding)
    {
        return is_bool($binding) ? json_encode($binding) : $binding;
    }

    /**
     * Compile a "JSON length" statement into SQL.
	 * 将"JSON长度"语句编译成SQL
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  string  $value
     * @return string
     */
    protected function compileJsonLength($column, $operator, $value)
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($column);

        return '(select count(*) from openjson('.$field.$path.')) '.$operator.' '.$value;
    }

    /**
     * {@inheritdoc}
     *
     * @param  array  $having
     * @return string
     */
    protected function compileHaving(array $having)
    {
        if ($having['type'] === 'Bitwise') {
            return $this->compileHavingBitwise($having);
        }

        return parent::compileHaving($having);
    }

    /**
     * Compile a having clause involving a bitwise operator.
	 * 编译包含位运算符的having子句
     *
     * @param  array  $having
     * @return string
     */
    protected function compileHavingBitwise($having)
    {
        $column = $this->wrap($having['column']);

        $parameter = $this->parameter($having['value']);

        return $having['boolean'].' ('.$column.' '.$having['operator'].' '.$parameter.') != 0';
    }

    /**
     * Create a full ANSI offset clause for the query.
	 * 为查询创建一个完整的ANSI偏移子句
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $components
     * @return string
     */
    protected function compileAnsiOffset(Builder $query, $components)
    {
        // An ORDER BY clause is required to make this offset query work, so if one does
        // not exist we'll just create a dummy clause to trick the database and so it
        // does not complain about the queries for not having an "order by" clause.
		// 要使这个偏移量查询工作，需要一个ORDER BY子句。
        if (empty($components['orders'])) {
            $components['orders'] = 'order by (select 0)';
        }

        // We need to add the row number to the query so we can compare it to the offset
        // and limit values given for the statements. So we will add an expression to
        // the "select" that will give back the row numbers on each of the records.
		// 我们需要将行号添加到查询中，以便将其与偏移量进行比较。
        $components['columns'] .= $this->compileOver($components['orders']);

        unset($components['orders']);

        if ($this->queryOrderContainsSubquery($query)) {
            $query->bindings = $this->sortBindingsForSubqueryOrderBy($query);
        }

        // Next we need to calculate the constraints that should be placed on the query
        // to get the right offset and limit from our query but if there is no limit
        // set we will just handle the offset only since that is all that matters.
		// 接下来，我们需要计算应该放在查询上的约束。
        $sql = $this->concatenate($components);

        return $this->compileTableExpression($sql, $query);
    }

    /**
     * Compile the over statement for a table expression.
	 * 编译表表达式的over语句
     *
     * @param  string  $orderings
     * @return string
     */
    protected function compileOver($orderings)
    {
        return ", row_number() over ({$orderings}) as row_num";
    }

    /**
     * Determine if the query's order by clauses contain a subquery.
	 * 确定查询的顺序子句是否包含子查询
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return bool
     */
    protected function queryOrderContainsSubquery($query)
    {
        if (! is_array($query->orders)) {
            return false;
        }

        return Arr::first($query->orders, function ($value) {
            return $this->isExpression($value['column'] ?? null);
        }, false) !== false;
    }

    /**
     * Move the order bindings to be after the "select" statement to account for an order by subquery.
	 * 将订单绑定移动到"select"语句之后，以说明按子查询的订单。
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return array
     */
    protected function sortBindingsForSubqueryOrderBy($query)
    {
        return Arr::sort($query->bindings, function ($bindings, $key) {
            return array_search($key, ['select', 'order', 'from', 'join', 'where', 'groupBy', 'having', 'union', 'unionOrder']);
        });
    }

    /**
     * Compile a common table expression for a query.
	 * 为查询编译公共表表达式
     *
     * @param  string  $sql
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return string
     */
    protected function compileTableExpression($sql, $query)
    {
        $constraint = $this->compileRowConstraint($query);

        return "select * from ({$sql}) as temp_table where row_num {$constraint} order by row_num";
    }

    /**
     * Compile the limit / offset row constraint for a query.
	 * 为查询编译限制/偏移行约束
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return string
     */
    protected function compileRowConstraint($query)
    {
        $start = (int) $query->offset + 1;

        if ($query->limit > 0) {
            $finish = (int) $query->offset + (int) $query->limit;

            return "between {$start} and {$finish}";
        }

        return ">= {$start}";
    }

    /**
     * Compile a delete statement without joins into SQL.
	 * 编译一个没有连接的delete语句到SQL中
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string  $table
     * @param  string  $where
     * @return string
     */
    protected function compileDeleteWithoutJoins(Builder $query, $table, $where)
    {
        $sql = parent::compileDeleteWithoutJoins($query, $table, $where);

        return ! is_null($query->limit) && $query->limit > 0 && $query->offset <= 0
                        ? Str::replaceFirst('delete', 'delete top ('.$query->limit.')', $sql)
                        : $sql;
    }

    /**
     * Compile the random statement into SQL.
	 * 将随机语句编译成SQL
     *
     * @param  string  $seed
     * @return string
     */
    public function compileRandom($seed)
    {
        return 'NEWID()';
    }

    /**
     * Compile the "limit" portions of the query.
	 * 编译查询的"limit"部分
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  int  $limit
     * @return string
     */
    protected function compileLimit(Builder $query, $limit)
    {
        return '';
    }

    /**
     * Compile the "offset" portions of the query.
	 * 编译查询的"偏移"部分
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  int  $offset
     * @return string
     */
    protected function compileOffset(Builder $query, $offset)
    {
        return '';
    }

    /**
     * Compile the lock into SQL.
	 * 将锁编译成SQL
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  bool|string  $value
     * @return string
     */
    protected function compileLock(Builder $query, $value)
    {
        return '';
    }

    /**
     * Wrap a union subquery in parentheses.
	 * 将联合子查询包装在括号中
     *
     * @param  string  $sql
     * @return string
     */
    protected function wrapUnion($sql)
    {
        return 'select * from ('.$sql.') as '.$this->wrapTable('temp_table');
    }

    /**
     * Compile an exists statement into SQL.
	 * 将exists语句编译成SQL
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return string
     */
    public function compileExists(Builder $query)
    {
        $existsQuery = clone $query;

        $existsQuery->columns = [];

        return $this->compileSelect($existsQuery->selectRaw('1 [exists]')->limit(1));
    }

    /**
     * Compile an update statement with joins into SQL.
	 * 将带有连接的更新语句编译成SQL
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string  $table
     * @param  string  $columns
     * @param  string  $where
     * @return string
     */
    protected function compileUpdateWithJoins(Builder $query, $table, $columns, $where)
    {
        $alias = last(explode(' as ', $table));

        $joins = $this->compileJoins($query, $query->joins);

        return "update {$alias} set {$columns} from {$table} {$joins} {$where}";
    }

    /**
     * Compile an "upsert" statement into SQL.
	 * 将"upsert"语句编译成SQL
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $values
     * @param  array  $uniqueBy
     * @param  array  $update
     * @return string
     */
    public function compileUpsert(Builder $query, array $values, array $uniqueBy, array $update)
    {
        $columns = $this->columnize(array_keys(reset($values)));

        $sql = 'merge '.$this->wrapTable($query->from).' ';

        $parameters = collect($values)->map(function ($record) {
            return '('.$this->parameterize($record).')';
        })->implode(', ');

        $sql .= 'using (values '.$parameters.') '.$this->wrapTable('laravel_source').' ('.$columns.') ';

        $on = collect($uniqueBy)->map(function ($column) use ($query) {
            return $this->wrap('laravel_source.'.$column).' = '.$this->wrap($query->from.'.'.$column);
        })->implode(' and ');

        $sql .= 'on '.$on.' ';

        if ($update) {
            $update = collect($update)->map(function ($value, $key) {
                return is_numeric($key)
                    ? $this->wrap($value).' = '.$this->wrap('laravel_source.'.$value)
                    : $this->wrap($key).' = '.$this->parameter($value);
            })->implode(', ');

            $sql .= 'when matched then update set '.$update.' ';
        }

        $sql .= 'when not matched then insert ('.$columns.') values ('.$columns.');';

        return $sql;
    }

    /**
     * Prepare the bindings for an update statement.
	 * 为更新语句准备绑定
     *
     * @param  array  $bindings
     * @param  array  $values
     * @return array
     */
    public function prepareBindingsForUpdate(array $bindings, array $values)
    {
        $cleanBindings = Arr::except($bindings, 'select');

        return array_values(
            array_merge($values, Arr::flatten($cleanBindings))
        );
    }

    /**
     * Compile the SQL statement to define a savepoint.
	 * 编译SQL语句以定义保存点
     *
     * @param  string  $name
     * @return string
     */
    public function compileSavepoint($name)
    {
        return 'SAVE TRANSACTION '.$name;
    }

    /**
     * Compile the SQL statement to execute a savepoint rollback.
	 * 编译SQL语句以执行保存点回滚
     *
     * @param  string  $name
     * @return string
     */
    public function compileSavepointRollBack($name)
    {
        return 'ROLLBACK TRANSACTION '.$name;
    }

    /**
     * Get the format for database stored dates.
	 * 获取数据库存储日期的格式
     *
     * @return string
     */
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s.v';
    }

    /**
     * Wrap a single string in keyword identifiers.
	 * 在关键字标识符中包装单个字符串
     *
     * @param  string  $value
     * @return string
     */
    protected function wrapValue($value)
    {
        return $value === '*' ? $value : '['.str_replace(']', ']]', $value).']';
    }

    /**
     * Wrap the given JSON selector.
	 * 包装给定的JSON选择器
     *
     * @param  string  $value
     * @return string
     */
    protected function wrapJsonSelector($value)
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($value);

        return 'json_value('.$field.$path.')';
    }

    /**
     * Wrap the given JSON boolean value.
	 * 包装给定的JSON布尔值
     *
     * @param  string  $value
     * @return string
     */
    protected function wrapJsonBooleanValue($value)
    {
        return "'".$value."'";
    }

    /**
     * Wrap a table in keyword identifiers.
	 * 用关键字标识符包装表
     *
     * @param  \Illuminate\Database\Query\Expression|string  $table
     * @return string
     */
    public function wrapTable($table)
    {
        if (! $this->isExpression($table)) {
            return $this->wrapTableValuedFunction(parent::wrapTable($table));
        }

        return $this->getValue($table);
    }

    /**
     * Wrap a table in keyword identifiers.
	 * 用关键字标识符包装表
     *
     * @param  string  $table
     * @return string
     */
    protected function wrapTableValuedFunction($table)
    {
        if (preg_match('/^(.+?)(\(.*?\))]$/', $table, $matches) === 1) {
            $table = $matches[1].']'.$matches[2];
        }

        return $table;
    }
}
