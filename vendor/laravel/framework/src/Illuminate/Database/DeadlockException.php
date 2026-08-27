<?php
/**
 * Illuminate，数据库，死锁异常
 */

namespace Illuminate\Database;

use PDOException;

class DeadlockException extends PDOException
{
    //
}
