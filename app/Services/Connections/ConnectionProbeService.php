<?php

namespace App\Services\Connections;

use App\Models\DataConnection;
use App\Services\Security\MaskingService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Throwable;

class ConnectionProbeService
{
    public function __construct(private readonly MaskingService $maskingService)
    {
    }

    public function probe(DataConnection $connection): array
    {
        $startedAt = microtime(true);

        if (! $connection->is_active) {
            return $this->persist($connection, [
                'status' => 'inactive',
                'message' => 'Connection is disabled.',
                'read_only_valid' => false,
                'response_time_ms' => null,
                'details' => [],
            ]);
        }

        if (! $connection->host || ! $connection->database_name) {
            return $this->persist($connection, [
                'status' => 'incomplete',
                'message' => 'Host or database name is missing.',
                'read_only_valid' => false,
                'response_time_ms' => null,
                'details' => [],
            ]);
        }

        $driver = $connection->db_driver === 'mariadb' ? 'mysql' : $connection->db_driver;
        $connectionName = 'probe_runtime';

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

        try {
            $db = DB::connection($connectionName);
            $db->select('select 1 as ok');

            $details = [
                'database' => $connection->database_name,
                'driver' => $connection->db_driver,
                'host_masked' => $connection->host_masked ?: $this->maskingService->maskHost($connection->host),
                'version' => $this->detectVersion($connectionName, $driver),
            ];

            [$readOnlyValid, $readOnlyNotes] = $this->validateReadOnlyPrivileges($connectionName, $driver);
            $details['read_only_notes'] = $readOnlyNotes;

            return $this->persist($connection, [
                'status' => 'ok',
                'message' => 'Connection successful.',
                'read_only_valid' => $readOnlyValid,
                'response_time_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                'details' => $details,
            ]);
        } catch (Throwable $exception) {
            return $this->persist($connection, [
                'status' => 'failed',
                'message' => $this->maskingService->sanitizeFreeText($exception->getMessage()) ?? 'Connection failed.',
                'read_only_valid' => false,
                'response_time_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                'details' => [],
            ]);
        } finally {
            DB::disconnect($connectionName);
            DB::purge($connectionName);
        }
    }

    private function detectVersion(string $connectionName, string $driver): ?string
    {
        $row = match ($driver) {
            'sqlsrv' => DB::connection($connectionName)->selectOne("select cast(serverproperty('ProductVersion') as varchar(50)) as version"),
            default => DB::connection($connectionName)->selectOne('select version() as version'),
        };

        return $row?->version ?? null;
    }

    private function validateReadOnlyPrivileges(string $connectionName, string $driver): array
    {
        if ($driver !== 'mysql' && $driver !== 'mariadb') {
            return [false, 'Read-only privilege inspection is not implemented for this driver yet.'];
        }

        try {
            $grants = DB::connection($connectionName)->select('show grants for current_user()');
            $serialized = json_encode($grants, JSON_THROW_ON_ERROR);
            $disallowed = ['INSERT', 'UPDATE', 'DELETE', 'DROP', 'ALTER', 'CREATE', 'TRUNCATE', 'GRANT'];

            foreach ($disallowed as $keyword) {
                if (stripos($serialized, $keyword) !== false) {
                    return [false, "Detected privilege keyword {$keyword} in grants."];
                }
            }

            return [true, 'No destructive privileges detected in SHOW GRANTS output.'];
        } catch (Throwable) {
            return [false, 'Grant inspection was not permitted by the remote database user.'];
        }
    }

    private function persist(DataConnection $connection, array $result): array
    {
        $connection->forceFill([
            'host_masked' => $connection->host_masked ?: $this->maskingService->maskHost($connection->host),
            'is_read_only_validated' => $result['read_only_valid'],
            'response_time_ms' => $result['response_time_ms'],
            'last_success_at' => $result['status'] === 'ok' ? now() : $connection->last_success_at,
            'last_failure_at' => $result['status'] !== 'ok' ? now() : $connection->last_failure_at,
            'last_failure_message' => $result['status'] !== 'ok' ? $result['message'] : null,
        ])->save();

        return $result;
    }
}
