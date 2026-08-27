<?php
/**
 * Illuminate，数据库，连接
 */

namespace Illuminate\Database;

use Carbon\CarbonInterval;
use Closure;
use DateTimeInterface;
use Doctrine\DBAL\Connection as DoctrineConnection;
use Doctrine\DBAL\Types\Type;
use Exception;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\StatementPrepared;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionCommitting;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Grammars\Grammar as QueryGrammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Support\Traits\Macroable;
use PDO;
use PDOStatement;
use RuntimeException;

class Connection implements ConnectionInterface
{
    use DetectsConcurrencyErrors,
        DetectsLostConnections,
        Concerns\ManagesTransactions,
        InteractsWithTime,
        Macroable;

    /**
     * The active PDO connection.
	 * 活动PDO连接
     *
     * @var \PDO|\Closure
     */
    protected $pdo;

    /**
     * The active PDO connection used for reads.
	 * 用于读取的活动PDO连接
     *
     * @var \PDO|\Closure
     */
    protected $readPdo;

    /**
     * The name of the connected database.
	 * 所连接数据库的名称
     *
     * @var string
     */
    protected $database;

    /**
     * The type of the connection.
	 * 连接的类型
     *
     * @var string|null
     */
    protected $readWriteType;

    /**
     * The table prefix for the connection.
	 * 连接的表前缀
     *
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * The database connection configuration options.
	 * 数据库连接配置选项
     *
     * @var array
     */
    protected $config = [];

    /**
     * The reconnector instance for the connection.
	 * 连接的reconnector实例
     *
     * @var callable
     */
    protected $reconnector;

    /**
     * The query grammar implementation.
	 * 查询语法实现
     *
     * @var \Illuminate\Database\Query\Grammars\Grammar
     */
    protected $queryGrammar;

    /**
     * The schema grammar implementation.
	 * 模式语法实现
     *
     * @var \Illuminate\Database\Schema\Grammars\Grammar
     */
    protected $schemaGrammar;

    /**
     * The query post processor implementation.
	 * 查询后处理器实现
     *
     * @var \Illuminate\Database\Query\Processors\Processor
     */
    protected $postProcessor;

    /**
     * The event dispatcher instance.
	 * 事件调度程序实例
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * The default fetch mode of the connection.
	 * 连接的默认获取模式
     *
     * @var int
     */
    protected $fetchMode = PDO::FETCH_OBJ;

    /**
     * The number of active transactions.
	 * 活动事务的数量
     *
     * @var int
     */
    protected $transactions = 0;

    /**
     * The transaction manager instance.
	 * 事务管理器实例
     *
     * @var \Illuminate\Database\DatabaseTransactionsManager
     */
    protected $transactionsManager;

    /**
     * Indicates if changes have been made to the database.
	 * 指示是否对数据库进行了更改
     *
     * @var bool
     */
    protected $recordsModified = false;

    /**
     * Indicates if the connection should use the "write" PDO connection.
	 * 指示连接是否应该使用"write"PDO连接
     *
     * @var bool
     */
    protected $readOnWriteConnection = false;

    /**
     * All of the queries run against the connection.
	 * 所有查询都针对连接运行
     *
     * @var array
     */
    protected $queryLog = [];

    /**
     * Indicates whether queries are being logged.
	 * 指示是否记录查询
     *
     * @var bool
     */
    protected $loggingQueries = false;

    /**
     * The duration of all executed queries in milliseconds.
	 * 所有已执行查询的持续时间，以毫秒为单位。
     *
     * @var float
     */
    protected $totalQueryDuration = 0.0;

    /**
     * All of the registered query duration handlers.
	 * 所有注册的查询持续时间处理程序
     *
     * @var array
     */
    protected $queryDurationHandlers = [];

    /**
     * Indicates if the connection is in a "dry run".
	 * 指示连接是否处于"试运行"状态
     *
     * @var bool
     */
    protected $pretending = false;

    /**
     * All of the callbacks that should be invoked before a query is executed.
	 * 在执行查询之前应该调用的所有回调函数
     *
     * @var \Closure[]
     */
    protected $beforeExecutingCallbacks = [];

    /**
     * The instance of Doctrine connection.
	 * 教条连接的实例
     *
     * @var \Doctrine\DBAL\Connection
     */
    protected $doctrineConnection;

    /**
     * Type mappings that should be registered with new Doctrine connections.
	 * 应该用新的Doctrine连接注册的类型映射
     *
     * @var array<string, string>
     */
    protected $doctrineTypeMappings = [];

    /**
     * The connection resolvers.
	 * 连接解析器
     *
     * @var \Closure[]
     */
    protected static $resolvers = [];

    /**
     * Create a new database connection instance.
	 * 创建一个新的数据库连接实例
     *
     * @param  \PDO|\Closure  $pdo
     * @param  string  $database
     * @param  string  $tablePrefix
     * @param  array  $config
     * @return void
     */
    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        $this->pdo = $pdo;

        // First we will setup the default properties. We keep track of the DB
        // name we are connected to since it is needed when some reflective
        // type commands are run such as checking whether a table exists.
		// 首先，我们将设置默认属性。我们跟踪DB。
        $this->database = $database;

        $this->tablePrefix = $tablePrefix;

        $this->config = $config;

        // We need to initialize a query grammar and the query post processors
        // which are both very important parts of the database abstractions
        // so we initialize these to their default values while starting.
		// 我们需要初始化查询语法和查询后置处理器。
        $this->useDefaultQueryGrammar();

        $this->useDefaultPostProcessor();
    }

    /**
     * Set the query grammar to the default implementation.
	 * 将查询语法设置为默认实现
     *
     * @return void
     */
    public function useDefaultQueryGrammar()
    {
        $this->queryGrammar = $this->getDefaultQueryGrammar();
    }

    /**
     * Get the default query grammar instance.
	 * 获取默认查询语法实例
     *
     * @return \Illuminate\Database\Query\Grammars\Grammar
     */
    protected function getDefaultQueryGrammar()
    {
        return new QueryGrammar;
    }

    /**
     * Set the schema grammar to the default implementation.
	 * 将模式语法设置为默认实现
     *
     * @return void
     */
    public function useDefaultSchemaGrammar()
    {
        $this->schemaGrammar = $this->getDefaultSchemaGrammar();
    }

    /**
     * Get the default schema grammar instance.
	 * 获取默认模式语法实例
     *
     * @return \Illuminate\Database\Schema\Grammars\Grammar
     */
    protected function getDefaultSchemaGrammar()
    {
        //
    }

    /**
     * Set the query post processor to the default implementation.
	 * 将查询后处理程序设置为默认实现
     *
     * @return void
     */
    public function useDefaultPostProcessor()
    {
        $this->postProcessor = $this->getDefaultPostProcessor();
    }

    /**
     * Get the default post processor instance.
	 * 获取默认的后处理器实例
     *
     * @return \Illuminate\Database\Query\Processors\Processor
     */
    protected function getDefaultPostProcessor()
    {
        return new Processor;
    }

    /**
     * Get a schema builder instance for the connection.
	 * 获取连接的架构构建器实例
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new SchemaBuilder($this);
    }

    /**
     * Begin a fluent query against a database table.
	 * 开始对数据库表进行流畅的查询
     *
     * @param  \Closure|\Illuminate\Database\Query\Builder|string  $table
     * @param  string|null  $as
     * @return \Illuminate\Database\Query\Builder
     */
    public function table($table, $as = null)
    {
        return $this->query()->from($table, $as);
    }

    /**
     * Get a new query builder instance.
	 * 获取一个新的查询生成器实例
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return new QueryBuilder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }

    /**
     * Run a select statement and return a single result.
	 * 运行一个select语句并返回一个结果
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     * @return mixed
     */
    public function selectOne($query, $bindings = [], $useReadPdo = true)
    {
        $records = $this->select($query, $bindings, $useReadPdo);

        return array_shift($records);
    }

    /**
     * Run a select statement and return the first column of the first row.
	 * 运行select语句并返回第一行的第一列
	 * 
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     * @return mixed
     *
     * @throws \Illuminate\Database\MultipleColumnsSelectedException
     */
    public function scalar($query, $bindings = [], $useReadPdo = true)
    {
        $record = $this->selectOne($query, $bindings, $useReadPdo);

        if (is_null($record)) {
            return null;
        }

        $record = (array) $record;

        if (count($record) > 1) {
            throw new MultipleColumnsSelectedException;
        }

        return reset($record);
    }

    /**
     * Run a select statement against the database.
	 * 对数据库运行一条选择语句
     *
     * @param  string  $query
     * @param  array  $bindings
     * @return array
     */
    public function selectFromWriteConnection($query, $bindings = [])
    {
        return $this->select($query, $bindings, false);
    }

    /**
     * Run a select statement against the database.
	 * 对数据库运行一条选择语句
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     * @return array
     */
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return [];
            }

            // For select statements, we'll simply execute the query and return an array
            // of the database result set. Each element in the array will be a single
            // row from the database table, and will either be an array or objects.
            $statement = $this->prepared(
                $this->getPdoForSelect($useReadPdo)->prepare($query)
            );

            $this->bindValues($statement, $this->prepareBindings($bindings));

            $statement->execute();

            return $statement->fetchAll();
        });
    }

    /**
     * Run a select statement against the database and returns a generator.
	 * 对数据库运行select语句并返回生成器
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     * @return \Generator
     */
    public function cursor($query, $bindings = [], $useReadPdo = true)
    {
        $statement = $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return [];
            }

            // First we will create a statement for the query. Then, we will set the fetch
            // mode and prepare the bindings for the query. Once that's done we will be
            // ready to execute the query against the database and return the cursor.
			// 首先，我们将为查询创建一条语句。然后，我们将设置取回。
            $statement = $this->prepared($this->getPdoForSelect($useReadPdo)
                              ->prepare($query));

            $this->bindValues(
                $statement, $this->prepareBindings($bindings)
            );

            // Next, we'll execute the query against the database and return the statement
            // so we can return the cursor. The cursor will use a PHP generator to give
            // back one row at a time without using a bunch of memory to render them.
            $statement->execute();

            return $statement;
        });

        while ($record = $statement->fetch()) {
            yield $record;
        }
    }

    /**
     * Configure the PDO prepared statement.
	 * 配置PDO prepared语句
     *
     * @param  \PDOStatement  $statement
     * @return \PDOStatement
     */
    protected function prepared(PDOStatement $statement)
    {
        $statement->setFetchMode($this->fetchMode);

        $this->event(new StatementPrepared($this, $statement));

        return $statement;
    }

    /**
     * Get the PDO connection to use for a select query.
	 * 获取要用于选择查询的PDO连接
     *
     * @param  bool  $useReadPdo
     * @return \PDO
     */
    protected function getPdoForSelect($useReadPdo = true)
    {
        return $useReadPdo ? $this->getReadPdo() : $this->getPdo();
    }

    /**
     * Run an insert statement against the database.
	 * 对数据库运行一条插入语句
     *
     * @param  string  $query
     * @param  array  $bindings
     * @return bool
     */
    public function insert($query, $bindings = [])
    {
        return $this->statement($query, $bindings);
    }

    /**
     * Run an update statement against the database.
	 * 对数据库运行一条更新语句
     *
     * @param  string  $query
     * @param  array  $bindings
     * @return int
     */
    public function update($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * Run a delete statement against the database.
	 * 对数据库运行delete语句
     *
     * @param  string  $query
     * @param  array  $bindings
     * @return int
     */
    public function delete($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * Execute an SQL statement and return the boolean result.
	 * 执行SQL语句并返回布尔结果
     *
     * @param  string  $query
     * @param  array  $bindings
     * @return bool
     */
    public function statement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return true;
            }

            $statement = $this->getPdo()->prepare($query);

            $this->bindValues($statement, $this->prepareBindings($bindings));

            $this->recordsHaveBeenModified();

            return $statement->execute();
        });
    }

    /**
     * Run an SQL statement and get the number of rows affected.
	 * 运行一条SQL语句，获取受影响的行数。
     *
     * @param  string  $query
     * @param  array  $bindings
     * @return int
     */
    public function affectingStatement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return 0;
            }

            // For update or delete statements, we want to get the number of rows affected
            // by the statement and return that back to the developer. We'll first need
            // to execute the statement and then we'll use PDO to fetch the affected.
			// 对于更新或删除语句，我们希望得到受影响的行数。
            $statement = $this->getPdo()->prepare($query);

            $this->bindValues($statement, $this->prepareBindings($bindings));

            $statement->execute();

            $this->recordsHaveBeenModified(
                ($count = $statement->rowCount()) > 0
            );

            return $count;
        });
    }

    /**
     * Run a raw, unprepared query against the PDO connection.
	 * 对PDO连接运行一个未准备的原始查询
     *
     * @param  string  $query
     * @return bool
     */
    public function unprepared($query)
    {
        return $this->run($query, [], function ($query) {
            if ($this->pretending()) {
                return true;
            }

            $this->recordsHaveBeenModified(
                $change = $this->getPdo()->exec($query) !== false
            );

            return $change;
        });
    }

    /**
     * Execute the given callback in "dry run" mode.
	 * 以"预演"模式执行给定的回调函数
     *
     * @param  \Closure  $callback
     * @return array
     */
    public function pretend(Closure $callback)
    {
        return $this->withFreshQueryLog(function () use ($callback) {
            $this->pretending = true;

            // Basically to make the database connection "pretend", we will just return
            // the default values for all the query methods, then we will return an
            // array of queries that were "executed" within the Closure callback.
            $callback($this);

            $this->pretending = false;

            return $this->queryLog;
        });
    }

    /**
     * Execute the given callback in "dry run" mode.
	 * 以"预演"模式执行给定的回调函数
     *
     * @param  \Closure  $callback
     * @return array
     */
    protected function withFreshQueryLog($callback)
    {
        $loggingQueries = $this->loggingQueries;

        // First we will back up the value of the logging queries property and then
        // we'll be ready to run callbacks. This query log will also get cleared
        // so we will have a new log of all the queries that are executed now.
		// 首先，我们将备份日志查询属性的值。
        $this->enableQueryLog();

        $this->queryLog = [];

        // Now we'll execute this callback and capture the result. Once it has been
        // executed we will restore the value of query logging and give back the
        // value of the callback so the original callers can have the results.
        $result = $callback();

        $this->loggingQueries = $loggingQueries;

        return $result;
    }

    /**
     * Bind values to their parameters in the given statement.
	 * 在给定语句中将值绑定到它们的参数
     *
     * @param  \PDOStatement  $statement
     * @param  array  $bindings
     * @return void
     */
    public function bindValues($statement, $bindings)
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                match (true) {
                    is_int($value) => PDO::PARAM_INT,
                    is_resource($value) => PDO::PARAM_LOB,
                    default => PDO::PARAM_STR
                },
            );
        }
    }

    /**
     * Prepare the query bindings for execution.
	 * 准备执行查询绑定
     *
     * @param  array  $bindings
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {
            // We need to transform all instances of DateTimeInterface into the actual
            // date string. Each query grammar maintains its own date string format
            // so we'll just ask the grammar for the format to get from the date.
            if ($value instanceof DateTimeInterface) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            } elseif (is_bool($value)) {
                $bindings[$key] = (int) $value;
            }
        }

        return $bindings;
    }

    /**
     * Run a SQL statement and log its execution context.
	 * 运行SQL语句并记录其执行上下文
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Illuminate\Database\QueryException
     */
    protected function run($query, $bindings, Closure $callback)
    {
        foreach ($this->beforeExecutingCallbacks as $beforeExecutingCallback) {
            $beforeExecutingCallback($query, $bindings, $this);
        }

        $this->reconnectIfMissingConnection();

        $start = microtime(true);

        // Here we will run this query. If an exception occurs we'll determine if it was
        // caused by a connection that has been lost. If that is the cause, we'll try
        // to re-establish connection and re-run the query with a fresh connection.
		// 这里我们将运行这个查询。如果出现异常，我们将确定它是否存在。
        try {
            $result = $this->runQueryCallback($query, $bindings, $callback);
        } catch (QueryException $e) {
            $result = $this->handleQueryException(
                $e, $query, $bindings, $callback
            );
        }

        // Once we have run the query we will calculate the time that it took to run and
        // then log the query, bindings, and execution time so we will report them on
        // the event that the developer needs them. We'll log time in milliseconds.
		// 一旦我们运行了查询，我们将计算运行和所花费的时间。
        $this->logQuery(
            $query, $bindings, $this->getElapsedTime($start)
        );

        return $result;
    }

    /**
     * Run a SQL statement.
	 * 运行SQL语句
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Illuminate\Database\QueryException
     */
    protected function runQueryCallback($query, $bindings, Closure $callback)
    {
        // To execute the statement, we'll simply call the callback, which will actually
        // run the SQL against the PDO connection. Then we can calculate the time it
        // took to execute and log the query SQL, bindings and time in our memory.
		// 要执行语句，我们只需调用回调函数，它实际上会。
        try {
            return $callback($query, $bindings);
        }

        // If an exception occurs when attempting to run a query, we'll format the error
        // message to include the bindings with SQL, which will make this exception a
        // lot more helpful to the developer instead of just the database's errors.
        catch (Exception $e) {
            throw new QueryException(
                $query, $this->prepareBindings($bindings), $e
            );
        }
    }

    /**
     * Log a query in the connection's query log.
	 * 在连接的查询日志中记录查询
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  float|null  $time
     * @return void
     */
    public function logQuery($query, $bindings, $time = null)
    {
        $this->totalQueryDuration += $time ?? 0.0;

        $this->event(new QueryExecuted($query, $bindings, $time, $this));

        if ($this->loggingQueries) {
            $this->queryLog[] = compact('query', 'bindings', 'time');
        }
    }

    /**
     * Get the elapsed time since a given starting point.
	 * 获取自给定起始点以来经过的时间
     *
     * @param  int  $start
     * @return float
     */
    protected function getElapsedTime($start)
    {
        return round((microtime(true) - $start) * 1000, 2);
    }

    /**
     * Register a callback to be invoked when the connection queries for longer than a given amount of time.
	 * 注册一个回调，以便在连接查询时间超过给定时间时调用。
     *
     * @param  \DateTimeInterface|\Carbon\CarbonInterval|float|int  $threshold
     * @param  callable  $handler
     * @return void
     */
    public function whenQueryingForLongerThan($threshold, $handler)
    {
        $threshold = $threshold instanceof DateTimeInterface
            ? $this->secondsUntil($threshold) * 1000
            : $threshold;

        $threshold = $threshold instanceof CarbonInterval
            ? $threshold->totalMilliseconds
            : $threshold;

        $this->queryDurationHandlers[] = [
            'has_run' => false,
            'handler' => $handler,
        ];

        $key = count($this->queryDurationHandlers) - 1;

        $this->listen(function ($event) use ($threshold, $handler, $key) {
            if (! $this->queryDurationHandlers[$key]['has_run'] && $this->totalQueryDuration() > $threshold) {
                $handler($this, $event);

                $this->queryDurationHandlers[$key]['has_run'] = true;
            }
        });
    }

    /**
     * Allow all the query duration handlers to run again, even if they have already run.
	 * 允许所有查询持续时间处理程序再次运行，即使它们已经运行。
     *
     * @return void
     */
    public function allowQueryDurationHandlersToRunAgain()
    {
        foreach ($this->queryDurationHandlers as $key => $queryDurationHandler) {
            $this->queryDurationHandlers[$key]['has_run'] = false;
        }
    }

    /**
     * Get the duration of all run queries in milliseconds.
	 * 获取以毫秒为单位的所有运行查询的持续时间
     *
     * @return float
     */
    public function totalQueryDuration()
    {
        return $this->totalQueryDuration;
    }

    /**
     * Reset the duration of all run queries.
	 * 重置所有运行查询的持续时间
     *
     * @return void
     */
    public function resetTotalQueryDuration()
    {
        $this->totalQueryDuration = 0.0;
    }

    /**
     * Handle a query exception.
	 * 处理查询异常
     *
     * @param  \Illuminate\Database\QueryException  $e
     * @param  string  $query
     * @param  array  $bindings
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Illuminate\Database\QueryException
     */
    protected function handleQueryException(QueryException $e, $query, $bindings, Closure $callback)
    {
        if ($this->transactions >= 1) {
            throw $e;
        }

        return $this->tryAgainIfCausedByLostConnection(
            $e, $query, $bindings, $callback
        );
    }

    /**
     * Handle a query exception that occurred during query execution.
	 * 处理查询执行期间发生的查询异常
     *
     * @param  \Illuminate\Database\QueryException  $e
     * @param  string  $query
     * @param  array  $bindings
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Illuminate\Database\QueryException
     */
    protected function tryAgainIfCausedByLostConnection(QueryException $e, $query, $bindings, Closure $callback)
    {
        if ($this->causedByLostConnection($e->getPrevious())) {
            $this->reconnect();

            return $this->runQueryCallback($query, $bindings, $callback);
        }

        throw $e;
    }

    /**
     * Reconnect to the database.
	 * 重新连接数据库
     *
     * @return mixed|false
     *
     * @throws \Illuminate\Database\LostConnectionException
     */
    public function reconnect()
    {
        if (is_callable($this->reconnector)) {
            $this->doctrineConnection = null;

            return call_user_func($this->reconnector, $this);
        }

        throw new LostConnectionException('Lost connection and no reconnector available.');
    }

    /**
     * Reconnect to the database if a PDO connection is missing.
	 * 如果缺少PDO连接，请重新连接数据库。
     *
     * @return void
     */
    protected function reconnectIfMissingConnection()
    {
        if (is_null($this->pdo)) {
            $this->reconnect();
        }
    }

    /**
     * Disconnect from the underlying PDO connection.
	 * 断开与底层PDO连接的连接
     *
     * @return void
     */
    public function disconnect()
    {
        $this->setPdo(null)->setReadPdo(null);

        $this->doctrineConnection = null;
    }

    /**
     * Register a hook to be run just before a database query is executed.
	 * 注册一个钩子，以便在执行数据库查询之前运行。
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function beforeExecuting(Closure $callback)
    {
        $this->beforeExecutingCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a database query listener with the connection.
	 * 向连接注册数据库查询侦听器
     *
     * @param  \Closure  $callback
     * @return void
     */
    public function listen(Closure $callback)
    {
        $this->events?->listen(Events\QueryExecuted::class, $callback);
    }

    /**
     * Fire an event for this connection.
	 * 为此连接触发一个事件
     *
     * @param  string  $event
     * @return array|null
     */
    protected function fireConnectionEvent($event)
    {
        return $this->events?->dispatch(match ($event) {
            'beganTransaction' => new TransactionBeginning($this),
            'committed' => new TransactionCommitted($this),
            'committing' => new TransactionCommitting($this),
            'rollingBack' => new TransactionRolledBack($this),
            default => null,
        });
    }

    /**
     * Fire the given event if possible.
	 * 如果可能，触发给定的事件。
     *
     * @param  mixed  $event
     * @return void
     */
    protected function event($event)
    {
        $this->events?->dispatch($event);
    }

    /**
     * Get a new raw query expression.
	 * 获取一个新的原始查询表达式
     *
     * @param  mixed  $value
     * @return \Illuminate\Database\Query\Expression
     */
    public function raw($value)
    {
        return new Expression($value);
    }

    /**
     * Determine if the database connection has modified any database records.
	 * 确定数据库连接是否修改了任何数据库记录
     *
     * @return bool
     */
    public function hasModifiedRecords()
    {
        return $this->recordsModified;
    }

    /**
     * Indicate if any records have been modified.
	 * 说明是否有任何记录被修改
     *
     * @param  bool  $value
     * @return void
     */
    public function recordsHaveBeenModified($value = true)
    {
        if (! $this->recordsModified) {
            $this->recordsModified = $value;
        }
    }

    /**
     * Set the record modification state.
	 * 设置记录修改状态
     *
     * @param  bool  $value
     * @return $this
     */
    public function setRecordModificationState(bool $value)
    {
        $this->recordsModified = $value;

        return $this;
    }

    /**
     * Reset the record modification state.
	 * 重置记录修改状态
     *
     * @return void
     */
    public function forgetRecordModificationState()
    {
        $this->recordsModified = false;
    }

    /**
     * Indicate that the connection should use the write PDO connection for reads.
	 * 指示该连接应该使用写PDO连接进行读操作
     *
     * @param  bool  $value
     * @return $this
     */
    public function useWriteConnectionWhenReading($value = true)
    {
        $this->readOnWriteConnection = $value;

        return $this;
    }

    /**
     * Is Doctrine available?
	 * 学说可用吗
     *
     * @return bool
     */
    public function isDoctrineAvailable()
    {
        return class_exists('Doctrine\DBAL\Connection');
    }

    /**
     * Indicates whether native alter operations will be used when dropping or renaming columns, even if Doctrine DBAL is installed.
	 * 指示在删除或重命名列时是否使用本机alter操作，即使安装了Doctrine DBAL。
     *
     * @return bool
     */
    public function usingNativeSchemaOperations()
    {
        return ! $this->isDoctrineAvailable() || SchemaBuilder::$alwaysUsesNativeSchemaOperationsIfPossible;
    }

    /**
     * Get a Doctrine Schema Column instance.
	 * 获取原则架构列实例
     *
     * @param  string  $table
     * @param  string  $column
     * @return \Doctrine\DBAL\Schema\Column
     */
    public function getDoctrineColumn($table, $column)
    {
        $schema = $this->getDoctrineSchemaManager();

        return $schema->listTableDetails($table)->getColumn($column);
    }

    /**
     * Get the Doctrine DBAL schema manager for the connection.
	 * 获取连接的Doctrine DBAL模式管理器
     *
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    public function getDoctrineSchemaManager()
    {
        $connection = $this->getDoctrineConnection();

        // Doctrine v2 expects one parameter while v3 expects two. 2nd will be ignored on v2...
        return $this->getDoctrineDriver()->getSchemaManager(
            $connection,
            $connection->getDatabasePlatform()
        );
    }

    /**
     * Get the Doctrine DBAL database connection instance.
	 * 获取Doctrine DBAL数据库连接实例
     *
     * @return \Doctrine\DBAL\Connection
     */
    public function getDoctrineConnection()
    {
        if (is_null($this->doctrineConnection)) {
            $driver = $this->getDoctrineDriver();

            $this->doctrineConnection = new DoctrineConnection(array_filter([
                'pdo' => $this->getPdo(),
                'dbname' => $this->getDatabaseName(),
                'driver' => $driver->getName(),
                'serverVersion' => $this->getConfig('server_version'),
            ]), $driver);

            foreach ($this->doctrineTypeMappings as $name => $type) {
                $this->doctrineConnection
                    ->getDatabasePlatform()
                    ->registerDoctrineTypeMapping($type, $name);
            }
        }

        return $this->doctrineConnection;
    }

    /**
     * Register a custom Doctrine mapping type.
	 * 注册一个自定义Doctrine映射类型
     *
     * @param  Type|class-string<Type>  $class
     * @param  string  $name
     * @param  string  $type
     * @return void
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \RuntimeException
     */
    public function registerDoctrineType(Type|string $class, string $name, string $type): void
    {
        if (! $this->isDoctrineAvailable()) {
            throw new RuntimeException(
                'Registering a custom Doctrine type requires Doctrine DBAL (doctrine/dbal).'
            );
        }

        if (! Type::hasType($name)) {
            Type::getTypeRegistry()
                ->register($name, is_string($class) ? new $class() : $class);
        }

        $this->doctrineTypeMappings[$name] = $type;
    }

    /**
     * Get the current PDO connection.
	 * 获取当前PDO连接
     *
     * @return \PDO
     */
    public function getPdo()
    {
        if ($this->pdo instanceof Closure) {
            return $this->pdo = call_user_func($this->pdo);
        }

        return $this->pdo;
    }

    /**
     * Get the current PDO connection parameter without executing any reconnect logic.
	 * 获取当前PDO连接参数，而不执行任何重新连接逻辑。
     *
     * @return \PDO|\Closure|null
     */
    public function getRawPdo()
    {
        return $this->pdo;
    }

    /**
     * Get the current PDO connection used for reading.
	 * 获取用于读取的当前PDO连接
     *
     * @return \PDO
     */
    public function getReadPdo()
    {
        if ($this->transactions > 0) {
            return $this->getPdo();
        }

        if ($this->readOnWriteConnection ||
            ($this->recordsModified && $this->getConfig('sticky'))) {
            return $this->getPdo();
        }

        if ($this->readPdo instanceof Closure) {
            return $this->readPdo = call_user_func($this->readPdo);
        }

        return $this->readPdo ?: $this->getPdo();
    }

    /**
     * Get the current read PDO connection parameter without executing any reconnect logic.
	 * 在不执行任何重连逻辑的情况下获取当前读PDO连接参数
     *
     * @return \PDO|\Closure|null
     */
    public function getRawReadPdo()
    {
        return $this->readPdo;
    }

    /**
     * Set the PDO connection.
	 * 设置PDO连接
     *
     * @param  \PDO|\Closure|null  $pdo
     * @return $this
     */
    public function setPdo($pdo)
    {
        $this->transactions = 0;

        $this->pdo = $pdo;

        return $this;
    }

    /**
     * Set the PDO connection used for reading.
	 * 设置用于读取的PDO连接
     *
     * @param  \PDO|\Closure|null  $pdo
     * @return $this
     */
    public function setReadPdo($pdo)
    {
        $this->readPdo = $pdo;

        return $this;
    }

    /**
     * Set the reconnect instance on the connection.
	 * 在连接上设置重新连接实例
     *
     * @param  callable  $reconnector
     * @return $this
     */
    public function setReconnector(callable $reconnector)
    {
        $this->reconnector = $reconnector;

        return $this;
    }

    /**
     * Get the database connection name.
	 * 获取数据库连接名称
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getConfig('name');
    }

    /**
     * Get the database connection full name.
	 * 获取数据库连接的全名
     *
     * @return string|null
     */
    public function getNameWithReadWriteType()
    {
        return $this->getName().($this->readWriteType ? '::'.$this->readWriteType : '');
    }

    /**
     * Get an option from the configuration options.
	 * 从配置选项中获取一个选项
     *
     * @param  string|null  $option
     * @return mixed
     */
    public function getConfig($option = null)
    {
        return Arr::get($this->config, $option);
    }

    /**
     * Get the PDO driver name.
	 * 获取PDO驱动程序名称
     *
     * @return string
     */
    public function getDriverName()
    {
        return $this->getConfig('driver');
    }

    /**
     * Get the query grammar used by the connection.
	 * 获取连接使用的查询语法
     *
     * @return \Illuminate\Database\Query\Grammars\Grammar
     */
    public function getQueryGrammar()
    {
        return $this->queryGrammar;
    }

    /**
     * Set the query grammar used by the connection.
	 * 设置连接使用的查询语法
     *
     * @param  \Illuminate\Database\Query\Grammars\Grammar  $grammar
     * @return $this
     */
    public function setQueryGrammar(Query\Grammars\Grammar $grammar)
    {
        $this->queryGrammar = $grammar;

        return $this;
    }

    /**
     * Get the schema grammar used by the connection.
	 * 获取连接使用的模式语法
     *
     * @return \Illuminate\Database\Schema\Grammars\Grammar
     */
    public function getSchemaGrammar()
    {
        return $this->schemaGrammar;
    }

    /**
     * Set the schema grammar used by the connection.
	 * 设置连接使用的模式语法
     *
     * @param  \Illuminate\Database\Schema\Grammars\Grammar  $grammar
     * @return $this
     */
    public function setSchemaGrammar(Schema\Grammars\Grammar $grammar)
    {
        $this->schemaGrammar = $grammar;

        return $this;
    }

    /**
     * Get the query post processor used by the connection.
	 * 获取连接使用的查询后处理程序
     *
     * @return \Illuminate\Database\Query\Processors\Processor
     */
    public function getPostProcessor()
    {
        return $this->postProcessor;
    }

    /**
     * Set the query post processor used by the connection.
	 * 设置连接使用的查询后处理器
     *
     * @param  \Illuminate\Database\Query\Processors\Processor  $processor
     * @return $this
     */
    public function setPostProcessor(Processor $processor)
    {
        $this->postProcessor = $processor;

        return $this;
    }

    /**
     * Get the event dispatcher used by the connection.
	 * 获取连接使用的事件调度程序
     *
     * @return \Illuminate\Contracts\Events\Dispatcher
     */
    public function getEventDispatcher()
    {
        return $this->events;
    }

    /**
     * Set the event dispatcher instance on the connection.
	 * 在连接上设置事件调度程序实例
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return $this
     */
    public function setEventDispatcher(Dispatcher $events)
    {
        $this->events = $events;

        return $this;
    }

    /**
     * Unset the event dispatcher for this connection.
	 * 取消此连接的事件调度程序的设置
     *
     * @return void
     */
    public function unsetEventDispatcher()
    {
        $this->events = null;
    }

    /**
     * Set the transaction manager instance on the connection.
	 * 在连接上设置事务管理器实例
     *
     * @param  \Illuminate\Database\DatabaseTransactionsManager  $manager
     * @return $this
     */
    public function setTransactionManager($manager)
    {
        $this->transactionsManager = $manager;

        return $this;
    }

    /**
     * Unset the transaction manager for this connection.
	 * 取消此连接的事务管理器设置
     *
     * @return void
     */
    public function unsetTransactionManager()
    {
        $this->transactionsManager = null;
    }

    /**
     * Determine if the connection is in a "dry run".
	 * 确定连接是否处于"预演"状态
     *
     * @return bool
     */
    public function pretending()
    {
        return $this->pretending === true;
    }

    /**
     * Get the connection query log.
	 * 获取连接查询日志
     *
     * @return array
     */
    public function getQueryLog()
    {
        return $this->queryLog;
    }

    /**
     * Clear the query log.
	 * 清除查询日志
     *
     * @return void
     */
    public function flushQueryLog()
    {
        $this->queryLog = [];
    }

    /**
     * Enable the query log on the connection.
	 * 在连接上启用查询日志
     *
     * @return void
     */
    public function enableQueryLog()
    {
        $this->loggingQueries = true;
    }

    /**
     * Disable the query log on the connection.
	 * 禁用连接上的查询日志
     *
     * @return void
     */
    public function disableQueryLog()
    {
        $this->loggingQueries = false;
    }

    /**
     * Determine whether we're logging queries.
	 * 确定我们是否记录查询
     *
     * @return bool
     */
    public function logging()
    {
        return $this->loggingQueries;
    }

    /**
     * Get the name of the connected database.
	 * 获取所连接数据库的名称
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->database;
    }

    /**
     * Set the name of the connected database.
	 * 设置所连接数据库的名称
     *
     * @param  string  $database
     * @return $this
     */
    public function setDatabaseName($database)
    {
        $this->database = $database;

        return $this;
    }

    /**
     * Set the read / write type of the connection.
	 * 设置连接的读写类型
     *
     * @param  string|null  $readWriteType
     * @return $this
     */
    public function setReadWriteType($readWriteType)
    {
        $this->readWriteType = $readWriteType;

        return $this;
    }

    /**
     * Get the table prefix for the connection.
	 * 获取连接的表前缀
     *
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * Set the table prefix in use by the connection.
	 * 设置连接使用的表前缀
     *
     * @param  string  $prefix
     * @return $this
     */
    public function setTablePrefix($prefix)
    {
        $this->tablePrefix = $prefix;

        $this->getQueryGrammar()->setTablePrefix($prefix);

        return $this;
    }

    /**
     * Set the table prefix and return the grammar.
	 * 设置表前缀并返回语法
     *
     * @param  \Illuminate\Database\Grammar  $grammar
     * @return \Illuminate\Database\Grammar
     */
    public function withTablePrefix(Grammar $grammar)
    {
        $grammar->setTablePrefix($this->tablePrefix);

        return $grammar;
    }

    /**
     * Register a connection resolver.
	 * 注册一个连接解析器
     *
     * @param  string  $driver
     * @param  \Closure  $callback
     * @return void
     */
    public static function resolverFor($driver, Closure $callback)
    {
        static::$resolvers[$driver] = $callback;
    }

    /**
     * Get the connection resolver for the given driver.
	 * 获取给定驱动程序的连接解析器
     *
     * @param  string  $driver
     * @return mixed
     */
    public static function getResolver($driver)
    {
        return static::$resolvers[$driver] ?? null;
    }
}
