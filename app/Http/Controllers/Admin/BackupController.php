<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    /**
     * Download a database backup.
     */
    public function download(Request $request): StreamedResponse
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");
        $database = config("database.connections.{$connection}.database");

        $filename = 'backup_' . date('Y-m-d_His') . ($driver === 'sqlite' ? '.sqlite' : '.sql');

        ActivityLog::record('created', null, "Mengunduh backup database.");

        if ($driver === 'sqlite') {
            return $this->downloadSqlite($database, $filename);
        }

        return $this->downloadMysql($filename);
    }

    private function downloadSqlite(string $path, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($path) {
            readfile($path);
        }, $filename, [
            'Content-Type' => 'application/x-sqlite3',
        ]);
    }

    private function downloadMysql(string $filename): StreamedResponse
    {
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        return response()->streamDownload(function () use ($host, $port, $database, $username, $password) {
            $tables = DB::select('SHOW TABLES');
            $key = "Tables_in_{$database}";

            echo "-- Database Backup: {$database}\n";
            echo "-- Date: " . date('Y-m-d H:i:s') . "\n";
            echo "-- -----------------------------------------------\n\n";
            echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                $tableName = $table->{$key};

                // Get create table statement
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $createSql = $createTable[0]->{'Create Table'} ?? '';

                echo "DROP TABLE IF EXISTS `{$tableName}`;\n";
                echo "{$createSql};\n\n";

                // Get data
                $rows = DB::table($tableName)->get();
                if ($rows->isNotEmpty()) {
                    $columns = array_keys((array) $rows->first());
                    $columnList = '`' . implode('`, `', $columns) . '`';

                    foreach ($rows->chunk(100) as $chunk) {
                        $values = [];
                        foreach ($chunk as $row) {
                            $rowValues = array_map(function ($value) {
                                if (is_null($value)) return 'NULL';
                                return "'" . addslashes((string) $value) . "'";
                            }, (array) $row);
                            $values[] = '(' . implode(', ', $rowValues) . ')';
                        }
                        echo "INSERT INTO `{$tableName}` ({$columnList}) VALUES\n" . implode(",\n", $values) . ";\n\n";
                    }
                }
            }

            echo "SET FOREIGN_KEY_CHECKS=1;\n";
        }, $filename, [
            'Content-Type' => 'application/sql',
        ]);
    }
}
