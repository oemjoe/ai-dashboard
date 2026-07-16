<?php

namespace App\Services\Connections;

use App\Models\DataConnection;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class RuntimeConnectionService
{
    public function forModel(DataConnection $connection): ConnectionInterface
    {
        $driver = $connection->db_driver === 'mariadb' ? 'mysql' : $connection->db_driver;
        $connectionName = 'runtime_'.preg_replace('/[^a-z0-9_]+/i', '_', $connection->slug);

        Config::set("database.connections.{$connectionName}", [
            'driver' => $driver,
            'host' => $connection->host,
            'port' => $connection->port,
            'database' => $connection->database_name,
            'username' => $connection->username,
            'password' => $connection->password_env_key ? env($connection->password_env_key) : null,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'options' => $connection->options ?? [],
            'encrypt' => false,
            'trust_server_certificate' => true,
        ]);

        DB::purge($connectionName);

        return DB::connection($connectionName);
    }

    public function disconnect(DataConnection $connection): void
    {
        $connectionName = 'runtime_'.preg_replace('/[^a-z0-9_]+/i', '_', $connection->slug);

        DB::disconnect($connectionName);
        DB::purge($connectionName);
    }
}
