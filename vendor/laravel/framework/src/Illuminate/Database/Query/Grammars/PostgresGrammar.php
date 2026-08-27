<?php
/**
 * Illuminate，数据库，PDO，语法，Postgres语法
 */

namespace Illuminate\Database\Query\Grammars;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PostgresGrammar extends Grammar
{
    /**
     * All of the available clause operators.
	 * 所有可用的子句操作符
     *
     * @var string[]
     */
    protected $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=',
        'like', 'not like', 'between', 'ilike', 'not ilike',
        '~', '&', '|', '#', '<<', '>>', '<<=', '>>=',
        '&&', '@>', '<@', '?', '?|', '?&', '||', '-', '@?', '@@', '#-',
        'is distinct from', 'is not distinct from',
    ];

    /**
     * The grammar specific bitwise operators.
	 * 语法特定的位操作符
     *
     * @var array
     */
    protected $bitwiseOperators = [
        '~', '&', '|', '#', '<<', '>>', '<<=', '>>=',
    ];

    /**
     * Compile a basic where clause.
	 * 编译一个基本的where子句
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereBasic(Builder $query, $where)
    {
        if (str_contains(strtolower($where['operator']), 'like')) {
            return sprintf(
                '%s::text %s %s',
                $this->wrap($where['column']),
                $where['operator'],
                $this->parameter($where['value'])
            );
        }

        return parent::whereBasic($query, $where);
    }

    /**
     * Compile a bitwise operator where clause.
	 * 编译位运算符where子句
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereBitwise(Builder $query, $where)
    {
        $value = $this->parameter($where['value']);

        $operator = str_replace('?', '??', $where['operator']);

        return '('.$this->wrap($where['column']).' '.$operator.' '.$value.')::bool';
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

        return $this->wrap($where['column']).'::date '.$where['operator'].' '.$value;
    }

    /**
     * Compile a "where time" clause.
	 * 编译一个"where time"子句
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereTime(Builder $query, $where)
    {
        $value = $this->parameter($where['value']);

        return $this->wrap($where['column']).'::time '.$where['operator'].' '.$value;
    }

    /**
     * Compile a date based where clause.
	 * 编译一个基于日期的where子句
     *
     * @param  string  $type
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function dateBasedWhere($type, Builder $query, $where)
    {
        $value = $this->parameter($where['value']);

        return 'extract('.$type.' from '.$this->wrap($where['column']).') '.$where['operator'].' '.$value;
    }

    /**
     * Compile a "where fulltext" clause.
	 * 编译一个"where全文"子句
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    public function whereFullText(Builder $query, $where)
    {
        $language = $where['options']['language'] ?? 'english';

        if (! in_array($language, $this->validFullTextLanguages())) {
            $language = 'english';
        }

        $columns = collect($where['columns'])->map(function ($column) use ($language) {
            return "to_tsvector('{$language}', {$this->wrap($column)})";
        })->implode(' || ');

        $mode = 'plainto_tsquery';

        if (($where['options']['mode'] ?? []) === 'phrase') {
            $mode = 'phraseto_tsquery';
        }

        if (($where['options']['mode'] ?? []) === 'websearch') {
            $mode = 'websearch_to_tsquery';
        }

        return "({$columns}) @@ {$mode}('{$language}', {$this->parameter($where['value'])})";
    }

    /**
     * Get an array of valid full text languages.
	 * 获取有效全文语言的数组
     *
     * @return array
     */
    protected function validFullTextLanguages()
    {
        return [
            'simple',
            'arabic',
            'danish',
            'dutch',
            'english',
            'finnish',
            'french',
            'german',
            'hungarian',
            'indonesian',
            'irish',
            'italian',
            'lithuanian',
            'nepali',
            'norwegian',
            'portuguese',
            'romanian',
            'russian',
            'spanish',
            'swedish',
            'tamil',
            'turkish',
        ];
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
        // If the query is actually performing an aggregating select, we will let that
        // compiler handle the building of the select clauses, as it will need some
        // more syntax that is best handled by that function to keep things neat.
		// 如果查询实际上正在执行聚合选择，
        if (! is_null($query->aggregate)) {
            return;
        }

        if (is_array($query->distinct)) {
            $select = 'select distinct on ('.$this->columnize($query->distinct).') ';
        } elseif ($query->distinct) {
            $select = 'select distinct ';
        } else {
            $select = 'select ';
        }

        return $select.$this->columnize($columns);
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
        $column = str_replace('->>', '->', $this->wrap($column));

        return '('.$column.')::jsonb @> '.$value;
    }

    /**
     * Compile a "JSON contains key" statement into SQL.
	 * 将"JSON contains key"语句编译成SQL
     *
     * @param  string  $column
     * @return string
     */
    protected function compileJsonContainsKey($column)
    {
        $segments = explode('->', $column);

        $lastSegment = array_pop($segments);

        if (filter_var($lastSegment, FILTER_VALIDATE_INT) !== false) {
            $i = $lastSegment;
        } elseif (preg_match('/\[(-?[0-9]+)\]$/', $lastSegment, $matches)) {
            $segments[] = Str::beforeLast($lastSegment, $matches[0]);

            $i = $matches[1];
        }

        $column = str_replace('->>', '->', $this->wrap(implode('->', $segments)));

        if (isset($i)) {
            return vsprintf('case when %s then %s else false end', [
                'jsonb_typeof(('.$column.")::jsonb) = 'array'",
                'jsonb_array_length(('.$column.')::jsonb) >= '.($i < 0 ? abs($i) : $i + 1),
            ]);
        }

        $key = "'".str_replace("'", "''", $lastSegment)."'";

        return 'coalesce(('.$column.')::jsonb ?? '.$key.', false)';
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
        $column = str_replace('->>', '->', $this->wrap($column));

        return 'jsonb_array_length(('.$column.')::jsonb) '.$operator.' '.$value;
    }

    /**
     * Compile a single having clause.
	 * 编译单个having子句
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

        return '('.$column.' '.$having['operator'].' '.$parameter.')::bool';
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
        if (! is_string($value)) {
            return $value ? 'for update' : 'for share';
        }

        return $value;
    }

    /**
     * Compile an insert ignore statement into SQL.
	 * 将插入忽略语句编译成SQL
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $values
     * @return string
     */
    public function compileInsertOrIgnore(Builder $query, array $values)
    {
        return $this->compileInsert($query, $values).' on conflict do nothing';
    }

    /**
     * Compile an insert and get ID statement into SQL.
	 * 将插入和获取ID语句编译成SQL
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $values
     * @param  string  $sequence
     * @return string
     */
    public function compileInsertGetId(Builder $query, $values, $sequence)
    {
        return $this->compileInsert($query, $values).' returning '.$this->wrap($sequence ?: 'id');
    }

    /**
     * Compile an update statement into SQL.
	 * 将update语句编译成SQL
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $values
     * @return string
     */
    public function compileUpdate(Builder $query, array $values)
    {
        if (isset($query->joins) || isset($query->limit)) {
            return $this->compileUpdateWithJoinsOrLimit($query, $values);
        }

        return parent::compileUpdate($query, $values);
    }

    /**
     * Compile the columns for an update statement.
	 * 编译更新语句的列
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $values
     * @return string
     */
    protected function compileUpdateColumns(Builder $query, array $values)
    {
        return collect($values)->map(function ($value, $key) {
            $column = last(explode('.', $key));

            if ($this->isJsonSelector($key)) {
                return $this->compileJsonUpdateColumn($column, $value);
            }

            return $this->wrap($column).' = '.$this->parameter($value);
        })->implode(', ');
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
        $sql = $this->compileInsert($query, $values);

        $sql .= ' on conflict ('.$this->columnize($uniqueBy).') do update set ';

        $columns = collect($update)->map(function ($value, $key) {
            return is_numeric($key)
                ? $this->wrap($value).' = '.$this->wrapValue('excluded').'.'.$this->wrap($value)
                : $this->wrap($key).' = '.$this->parameter($value);
        })->implode(', ');

        return $sql.$columns;
    }

    /**
     * Prepares a JSON column being updated using the JSONB_SET function.
	 * 使用JSONB_SET函数准备要更新的JSON列
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return string
     */
    protected function compileJsonUpdateColumn($key, $value)
    {
        $segments = explode('->', $key);

        $field = $this->wrap(array_shift($segments));

        $path = "'{".implode(',', $this->wrapJsonPathAttributes($segments, '"'))."}'";

        return "{$field} = jsonb_set({$field}::jsonb, {$path}, {$this->parameter($value)})";
    }

    /**
     * Compile an update from statement into SQL.
	 * 将update from语句编译为SQL
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $values
     * @return string
     */
    public function compileUpdateFrom(Builder $query, $values)
    {
        $table = $this->wrapTable($query->from);

        // Each one of the columns in the update statements needs to be wrapped in the
        // keyword identifiers, also a place-holder needs to be created for each of
        // the values in the list of bindings so we can make the sets statements.
		// 更新语句中的每个列都需要。
        $columns = $this->compileUpdateColumns($query, $values);

        $from = '';

        if (isset($query->joins)) {
            // When using Postgres, updates with joins list the joined tables in the from
            // clause, which is different than other systems like MySQL. Here, we will
            // compile out the tables that are joined and add them to a from clause.
            $froms = collect($query->joins)->map(function ($join) {
                return $this->wrapTable($join->table);
            })->all();

            if (count($froms) > 0) {
                $from = ' from '.implode(', ', $froms);
            }
        }

        $where = $this->compileUpdateWheres($query);

        return trim("update {$table} set {$columns}{$from} {$where}");
    }

    /**
     * Compile the additional where clauses for updates with joins.
	 * 编译用于连接更新的附加where子句
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return string
     */
    protected function compileUpdateWheres(Builder $query)
    {
        $baseWheres = $this->compileWheres($query);

        if (! isset($query->joins)) {
            return $baseWheres;
        }

        // Once we compile the join constraints, we will either use them as the where
        // clause or append them to the existing base where clauses. If we need to
        // strip the leading boolean we will do so when using as the only where.
		// 一旦编译了连接约束，我们将使用它们作为where。
        $joinWheres = $this->compileUpdateJoinWheres($query);

        if (trim($baseWheres) == '') {
            return 'where '.$this->removeLeadingBoolean($joinWheres);
        }

        return $baseWheres.' '.$joinWheres;
    }

    /**
     * Compile the "join" clause where clauses for an update.
	 * 编译"join"子句，其中的子句用于更新。
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return string
     */
    protected function compileUpdateJoinWheres(Builder $query)
    {
        $joinWheres = [];

        // Here we will just loop through all of the join constraints and compile them
        // all out then implode them. This should give us "where" like syntax after
        // everything has been built and then we will join it to the real wheres.
		// 这里我们将循环遍历所有连接约束并编译它们。
        foreach ($query->joins as $join) {
            foreach ($join->wheres as $where) {
                $method = "where{$where['type']}";

                $joinWheres[] = $where['boolean'].' '.$this->$method($query, $where);
            }
        }

        return implode(' ', $joinWheres);
    }

    /**
     * Prepare the bindings for an update statement.
	 * 为更新语句准备绑定
     *
     * @param  array  $bindings
     * @param  array  $values
     * @return array
     */
    public function prepareBindingsForUpdateFrom(array $bindings, array $values)
    {
        $values = collect($values)->map(function ($value, $column) {
            return is_array($value) || ($this->isJsonSelector($column) && ! $this->isExpression($value))
                ? json_encode($value)
                : $value;
        })->all();

        $bindingsWithoutWhere = Arr::except($bindings, ['select', 'where']);

        return array_values(
            array_merge($values, $bindings['where'], Arr::flatten($bindingsWithoutWhere))
        );
    }

    /**
     * Compile an update statement with joins or limit into SQL.
	 * 将带有连接或限制的更新语句编译成SQL
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $values
     * @return string
     */
    protected function compileUpdateWithJoinsOrLimit(Builder $query, array $values)
    {
        $table = $this->wrapTable($query->from);

        $columns = $this->compileUpdateColumns($query, $values);

        $alias = last(preg_split('/\s+as\s+/i', $query->from));

        $selectSql = $this->compileSelect($query->select($alias.'.ctid'));

        return "update {$table} set {$columns} where {$this->wrap('ctid')} in ({$selectSql})";
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
        $values = collect($values)->map(function ($value, $column) {
            return is_array($value) || ($this->isJsonSelector($column) && ! $this->isExpression($value))
                ? json_encode($value)
                : $value;
        })->all();

        $cleanBindings = Arr::except($bindings, 'select');

        return array_values(
            array_merge($values, Arr::flatten($cleanBindings))
        );
    }

    /**
     * Compile a delete statement into SQL.
	 * 将delete语句编译成SQL
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return string
     */
    public function compileDelete(Builder $query)
    {
        if (isset($query->joins) || isset($query->limit)) {
            return $this->compileDeleteWithJoinsOrLimit($query);
        }

        return parent::compileDelete($query);
    }

    /**
     * Compile a delete statement with joins or limit into SQL.
	 * 将带有连接或限制的删除语句编译成SQL
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return string
     */
    protected function compileDeleteWithJoinsOrLimit(Builder $query)
    {
        $table = $this->wrapTable($query->from);

        $alias = last(preg_split('/\s+as\s+/i', $query->from));

        $selectSql = $this->compileSelect($query->select($alias.'.ctid'));

        return "delete from {$table} where {$this->wrap('ctid')} in ({$selectSql})";
    }

    /**
     * Compile a truncate table statement into SQL.
	 * 将截断表语句编译成SQL
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return array
     */
    public function compileTruncate(Builder $query)
    {
        return ['truncate '.$this->wrapTable($query->from).' restart identity cascade' => []];
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
        $path = explode('->', $value);

        $field = $this->wrapSegments(explode('.', array_shift($path)));

        $wrappedPath = $this->wrapJsonPathAttributes($path);

        $attribute = array_pop($wrappedPath);

        if (! empty($wrappedPath)) {
            return $field.'->'.implode('->', $wrappedPath).'->>'.$attribute;
        }

        return $field.'->>'.$attribute;
    }

    /**
     * Wrap the given JSON selector for boolean values.
	 * 将给定的JSON选择器包装为布尔值
     *
     * @param  string  $value
     * @return string
     */
    protected function wrapJsonBooleanSelector($value)
    {
        $selector = str_replace(
            '->>', '->',
            $this->wrapJsonSelector($value)
        );

        return '('.$selector.')::jsonb';
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
        return "'".$value."'::jsonb";
    }

    /**
     * Wrap the attributes of the given JSON path.
	 * 包装给定JSON路径的属性
     *
     * @param  array  $path
     * @return array
     */
    protected function wrapJsonPathAttributes($path)
    {
        $quote = func_num_args() === 2 ? func_get_arg(1) : "'";

        return collect($path)->map(function ($attribute) {
            return $this->parseJsonPathArrayKeys($attribute);
        })->collapse()->map(function ($attribute) use ($quote) {
            return filter_var($attribute, FILTER_VALIDATE_INT) !== false
                        ? $attribute
                        : $quote.$attribute.$quote;
        })->all();
    }

    /**
     * Parse the given JSON path attribute for array keys.
	 * 为数组键解析给定的JSON路径属性
     *
     * @param  string  $attribute
     * @return array
     */
    protected function parseJsonPathArrayKeys($attribute)
    {
        if (preg_match('/(\[[^\]]+\])+$/', $attribute, $parts)) {
            $key = Str::beforeLast($attribute, $parts[0]);

            preg_match_all('/\[([^\]]+)\]/', $parts[0], $keys);

            return collect([$key])
                ->merge($keys[1])
                ->diff('')
                ->values()
                ->all();
        }

        return [$attribute];
    }
}
