<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final readonly class ListLocalDatabaseTables
{
    /**
     * @return list<array{name: string, schema: string|null, column_count: int, record_count: int}>
     */
    public function handle(): array
    {
        $tables = [];

        foreach (Schema::getTables() as $table) {
            if (is_array($table) && is_scalar($table['name'] ?? null)) {
                $tables[] = $this->summarizeTable($table);
            }
        }

        usort($tables, fn (array $first, array $second): int => $first['name'] <=> $second['name']);

        return $tables;
    }

    public function exists(string $table): bool
    {
        foreach (Schema::getTables() as $schemaTable) {
            if (is_array($schemaTable) && ($schemaTable['name'] ?? null) === $table) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<mixed>  $table
     * @return array{name: string, schema: string|null, column_count: int, record_count: int}
     */
    private function summarizeTable(array $table): array
    {
        $name = is_scalar($table['name'] ?? null) ? (string) $table['name'] : '';

        return [
            'name' => $name,
            'schema' => is_string($table['schema'] ?? null) ? $table['schema'] : null,
            'column_count' => count(Schema::getColumns($name)),
            'record_count' => (int) DB::table($name)->count(),
        ];
    }
}
