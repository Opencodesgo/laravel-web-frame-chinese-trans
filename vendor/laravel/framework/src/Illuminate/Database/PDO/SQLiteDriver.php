<?php
/**
 * Illuminate，数据库，PDO，SQLite驱动
 */

namespace Illuminate\Database\PDO;

use Doctrine\DBAL\Driver\AbstractSQLiteDriver;
use Illuminate\Database\PDO\Concerns\ConnectsToDatabase;

class SQLiteDriver extends AbstractSQLiteDriver
{
    use ConnectsToDatabase;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pdo_sqlite';
    }
}
