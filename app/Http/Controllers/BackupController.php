<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\ActivityLog;

class BackupController extends Controller
{
    public function index()
    {
        $tables = [
            'users',
            'departments',
            'roles',
            'categories',
            'suppliers',
            'inventory_items',
            'inventory_histories',
            'purchase_orders',
            'purchase_order_items',
            'deliveries',
            'delivery_items',
            'supply_requests',
            'supply_request_items',
            'issuances',
            'issuance_items',
            'returns',
            'notifications',
            'activity_logs',
        ];

        $tableStats = [];
        $totalRecords = 0;

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                $tableStats[] = [
                    'name'  => $table,
                    'count' => $count,
                ];
                $totalRecords += $count;
            }
        }

        // Database size estimate
        $dbName = config('database.connections.mysql.database');
        $dbSizeMb = 0;
        try {
            $sizeResult = DB::select("
                SELECT SUM(data_length + index_length) / 1024 / 1024 AS size_mb 
                FROM information_schema.TABLES 
                WHERE table_schema = ?
            ", [$dbName]);
            if (!empty($sizeResult)) {
                $dbSizeMb = round((float)($sizeResult[0]->size_mb ?? 0), 2);
            }
        } catch (\Throwable $e) {
            $dbSizeMb = 0;
        }

        return view('admin.backups.index', compact('tableStats', 'totalRecords', 'dbSizeMb'));
    }

    public function downloadSql()
    {
        $tables = [
            'users', 'departments', 'roles', 'model_has_roles', 'permissions', 'role_has_permissions',
            'categories', 'suppliers', 'inventory_items', 'inventory_histories',
            'purchase_orders', 'purchase_order_items', 'procurement_approvals',
            'deliveries', 'delivery_items', 'supply_requests', 'supply_request_items',
            'issuances', 'issuance_items', 'returns', 'notifications', 'activity_logs',
        ];

        $filename = 'backup-supply-system-' . date('Y-m-d_H-i-s') . '.sql';

        ActivityLog::log('downloaded', 'backup', 'Downloaded SQL database backup', auth()->user());

        return response()->streamDownload(function () use ($tables) {
            echo "-- ==============================================\n";
            echo "-- Supply Management System Database Backup\n";
            echo "-- Date: " . date('Y-m-d H:i:s') . "\n";
            echo "-- ==============================================\n\n";
            echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                if (!Schema::hasTable($table)) continue;

                echo "-- Table structure for `$table`\n";
                echo "DROP TABLE IF EXISTS `$table`;\n";
                try {
                    $createStmt = DB::select("SHOW CREATE TABLE `$table`");
                    if (!empty($createStmt)) {
                        $createSql = ((array)$createStmt[0])['Create Table'] ?? '';
                        echo $createSql . ";\n\n";
                    }
                } catch (\Throwable $e) {
                    echo "-- Could not generate CREATE TABLE syntax for $table\n\n";
                }

                $rows = DB::table($table)->get();
                if ($rows->count() > 0) {
                    echo "-- Dumping data for `$table`\n";
                    foreach ($rows as $row) {
                        $rowArray = (array)$row;
                        $columns = array_keys($rowArray);
                        $escapedValues = array_map(function ($val) {
                            if (is_null($val)) return 'NULL';
                            return "'" . addslashes((string)$val) . "'";
                        }, array_values($rowArray));

                        echo "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $escapedValues) . ");\n";
                    }
                    echo "\n";
                }
            }

            echo "SET FOREIGN_KEY_CHECKS=1;\n";
        }, $filename, [
            'Content-Type' => 'text/x-sql',
        ]);
    }

    public function downloadJson()
    {
        $tables = [
            'users', 'departments', 'categories', 'suppliers',
            'inventory_items', 'purchase_orders', 'purchase_order_items',
            'deliveries', 'delivery_items', 'supply_requests', 'supply_request_items',
            'issuances', 'issuance_items', 'returns', 'notifications', 'activity_logs',
        ];

        $exportData = [
            'system'     => 'Supply Management System',
            'version'    => '1.0.0',
            'exported_at'=> date('Y-m-d H:i:s'),
            'exported_by'=> auth()->user()->name ?? 'Admin',
            'tables'     => [],
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $exportData['tables'][$table] = DB::table($table)->get()->toArray();
            }
        }

        $filename = 'backup-supply-system-' . date('Y-m-d_H-i-s') . '.json';

        ActivityLog::log('downloaded', 'backup', 'Downloaded JSON database backup', auth()->user());

        return response()->streamDownload(function () use ($exportData) {
            echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,json,txt|max:20480',
        ], [
            'backup_file.required' => 'Please select a backup file to restore.',
            'backup_file.mimes'    => 'The backup file must be a .sql or .json file.',
            'backup_file.max'      => 'Backup file size cannot exceed 20 MB.',
        ]);

        $file = $request->file('backup_file');
        $ext  = strtolower($file->getClientOriginalExtension());
        $content = file_get_contents($file->getRealPath());

        try {
            if ($ext === 'sql' || str_contains($content, 'INSERT INTO')) {
                DB::unprepared("SET FOREIGN_KEY_CHECKS=0;\n" . $content . "\nSET FOREIGN_KEY_CHECKS=1;");
            } elseif ($ext === 'json') {
                $jsonData = json_decode($content, true);
                if (isset($jsonData['tables']) && is_array($jsonData['tables'])) {
                    DB::statement('SET FOREIGN_KEY_CHECKS=0');
                    foreach ($jsonData['tables'] as $table => $rows) {
                        if (Schema::hasTable($table) && is_array($rows)) {
                            DB::table($table)->truncate();
                            foreach ($rows as $row) {
                                DB::table($table)->insert((array)$row);
                            }
                        }
                    }
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                } else {
                    return back()->with('error', 'Invalid JSON backup format.');
                }
            }

            ActivityLog::log('restored', 'backup', 'Restored system database from backup file', auth()->user());
            return back()->with('success', 'Database backup restored successfully!');

        } catch (\Throwable $e) {
            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }
}
