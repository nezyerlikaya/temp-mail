<?php

namespace App\Services\Backups;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ZipArchive;

class DatabaseBackupAction
{
    /** @return array{tables: int, rows: int} */
    public function writeTo(ZipArchive $zip): array
    {
        $tables = $this->tableNames();
        $rows = 0;

        foreach ($tables as $table) {
            $records = DB::table($table)->get()->map(fn (object $row): array => (array) $row)->all();
            $rows += count($records);

            $zip->addFromString('database/'.$table.'.json', json_encode($records, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
        }

        $zip->addFromString('database/schema.json', json_encode([
            'connection' => config('database.default'),
            'tables' => $tables,
        ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));

        return ['tables' => count($tables), 'rows' => $rows];
    }

    /** @return array<int, string> */
    private function tableNames(): array
    {
        return collect(Schema::getTables())
            ->map(fn (array $table): string => (string) ($table['name'] ?? $table['tablename'] ?? ''))
            ->filter()
            ->reject(fn (string $table): bool => $table === 'migrations')
            ->values()
            ->all();
    }
}
