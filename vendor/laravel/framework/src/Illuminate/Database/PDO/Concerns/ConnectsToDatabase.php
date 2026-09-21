<?php
/**
 * Illuminate，数据库，PDO，问题，连接数据库
 */

namespace Illuminate\Database\PDO\Concerns;

use Illuminate\Database\PDO\Connection;
use InvalidArgumentException;
use PDO;

trait ConnectsToDatabase
{
    /**
     * Create a new database connection.
	 * 创建新的数据库连接
     *
     * @param  mixed[]  $params
     * @param  string|null  $username
     * @param  string|null  $password
     * @param  mixed[]  $driverOptions
     * @return \Illuminate\Database\PDO\Connection
     *
     * @throws \InvalidArgumentException
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = [])
    {
        if (! isset($params['pdo']) || ! $params['pdo'] instanceof PDO) {
            throw new InvalidArgumentException('Laravel requires the "pdo" property to be set and be a PDO instance.');
        }

        return new Connection($params['pdo']);
    }
}
