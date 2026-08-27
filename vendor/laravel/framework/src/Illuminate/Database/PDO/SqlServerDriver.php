<?php
/**
 * Illuminate，数据库，PDO，Sql Server 驱动
 */

namespace Illuminate\Database\PDO;

use Doctrine\DBAL\Driver\AbstractSQLServerDriver;

class SqlServerDriver extends AbstractSQLServerDriver
{
    /**
     * Create a new database connection.
	 * 创建一个新的数据库连接
     *
     * @param  mixed[]  $params
     * @param  string|null  $username
     * @param  string|null  $password
     * @param  mixed[]  $driverOptions
     * @return \Illuminate\Database\PDO\SqlServerConnection
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = [])
    {
        return new SqlServerConnection(
            new Connection($params['pdo'])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pdo_sqlsrv';
    }
}
