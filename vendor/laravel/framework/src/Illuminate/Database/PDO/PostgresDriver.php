<?php
/**
 * Illuminate，数据库，PDO，Postgres驱动
 */

namespace Illuminate\Database\PDO;

use Doctrine\DBAL\Driver\AbstractPostgreSQLDriver;
use Illuminate\Database\PDO\Concerns\ConnectsToDatabase;

class PostgresDriver extends AbstractPostgreSQLDriver
{
    use ConnectsToDatabase;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pdo_pgsql';
    }
}
