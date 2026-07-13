<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

final readonly class InspectLocalDatabaseTable
{
    private const int PER_PAGE = 25;

    private const string REDACTED_VALUE = '[censurado]';

    /** @var list<string> */
    private const array SENSITIVE_COLUMN_FRAGMENTS = [
        'authorization',
        'cookie',
        'credential',
        'passphrase',
        'password',
        'recovery_codes',
        'secret',
        'signature',
        'token',
    ];

    /** @var list<string> */
    private const array SENSITIVE_EXACT_COLUMNS = [
        'access_key',
        'api_key',
        'auth_key',
        'encryption_key',
        'exception',
        'failed_job_ids',
        'options',
        'payload',
        'private_key',
        'signing_key',
    ];

    /** @var list<string> */
    private const array SENSITIVE_TABLE_FRAGMENTS = [
        'cache',
        'config',
        'credential',
        'key',
        'secret',
        'session',
        'setting',
        'token',
    ];

    /** @var list<string> */
    private const array GENERIC_SECRET_COLUMNS = [
        'content',
        'data',
        'value',
    ];

    /**
     * @return array{
     *     name: string,
     *     columns: list<array{name: string, type: string, nullable: bool, default: scalar|null, auto_increment: bool}>,
     *     indexes: list<array{name: string, columns: list<string>, unique: bool, primary: bool}>,
     *     foreign_keys: list<array{name: string, columns: list<string>, foreign_table: string|null, foreign_columns: list<string>}>,
     *     records: array{data: list<array<string, scalar|null>>, current_page: int, last_page: int, per_page: int, total: int, from: int|null, to: int|null, next_page_url: string|null, prev_page_url: string|null},
     *     redacted_columns: list<string>
     * }
     */
    public function handle(string $table): array
    {
        $columns = $this->columns($table);
        $redactedColumns = $this->redactedColumns($table, $columns);

        return [
            'name' => $table,
            'columns' => $columns,
            'indexes' => $this->indexes($table),
            'foreign_keys' => $this->foreignKeys($table),
            'records' => $this->records($table, $columns, $redactedColumns),
            'redacted_columns' => $redactedColumns,
        ];
    }

    /**
     * @return list<array{name: string, type: string, nullable: bool, default: scalar|null, auto_increment: bool}>
     */
    private function columns(string $table): array
    {
        $columns = [];

        foreach (Schema::getColumns($table) as $column) {
            if (! is_array($column)) {
                continue;
            }

            if (! is_scalar($column['name'] ?? null)) {
                continue;
            }

            $name = (string) $column['name'];
            $default = $this->scalarOrNull($column['default'] ?? null);

            $columns[] = [
                'name' => $name,
                'type' => is_scalar($column['type'] ?? null)
                    ? (string) $column['type']
                    : (is_scalar($column['type_name'] ?? null) ? (string) $column['type_name'] : 'unknown'),
                'nullable' => (bool) ($column['nullable'] ?? false),
                'default' => $this->isSensitiveColumn($table, $name) && $default !== null
                    ? self::REDACTED_VALUE
                    : $default,
                'auto_increment' => (bool) ($column['auto_increment'] ?? false),
            ];
        }

        return $columns;
    }

    /**
     * @return list<array{name: string, columns: list<string>, unique: bool, primary: bool}>
     */
    private function indexes(string $table): array
    {
        $indexes = [];

        foreach (Schema::getIndexes($table) as $index) {
            if (! is_array($index)) {
                continue;
            }

            $indexes[] = [
                'name' => is_scalar($index['name'] ?? null) ? (string) $index['name'] : '',
                'columns' => $this->stringList($index['columns'] ?? []),
                'unique' => (bool) ($index['unique'] ?? false),
                'primary' => (bool) ($index['primary'] ?? false),
            ];
        }

        return $indexes;
    }

    /**
     * @return list<array{name: string, columns: list<string>, foreign_table: string|null, foreign_columns: list<string>}>
     */
    private function foreignKeys(string $table): array
    {
        $foreignKeys = [];

        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            if (! is_array($foreignKey)) {
                continue;
            }

            $foreignKeys[] = [
                'name' => is_scalar($foreignKey['name'] ?? null) ? (string) $foreignKey['name'] : '',
                'columns' => $this->stringList($foreignKey['columns'] ?? []),
                'foreign_table' => is_string($foreignKey['foreign_table'] ?? null) ? $foreignKey['foreign_table'] : null,
                'foreign_columns' => $this->stringList($foreignKey['foreign_columns'] ?? []),
            ];
        }

        return $foreignKeys;
    }

    /**
     * @param  list<array{name: string, type: string, nullable: bool, default: scalar|null, auto_increment: bool}>  $columns
     * @param  list<string>  $redactedColumns
     * @return array{data: list<array<string, scalar|null>>, current_page: int, last_page: int, per_page: int, total: int, from: int|null, to: int|null, next_page_url: string|null, prev_page_url: string|null}
     */
    private function records(string $table, array $columns, array $redactedColumns): array
    {
        $paginator = DB::table($table)
            ->orderBy($this->orderColumn($columns))
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return [
            'data' => $this->recordsData($paginator->items(), $redactedColumns),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'next_page_url' => $paginator->nextPageUrl(),
            'prev_page_url' => $paginator->previousPageUrl(),
        ];
    }

    /**
     * @param  list<array{name: string, type: string, nullable: bool, default: scalar|null, auto_increment: bool}>  $columns
     */
    private function orderColumn(array $columns): string
    {
        foreach ($columns as $column) {
            if ($column['name'] === 'id') {
                return $column['name'];
            }
        }

        return $columns[0]['name'] ?? throw new RuntimeException('Cannot inspect a table without columns.');
    }

    /**
     * @param  list<array{name: string, type: string, nullable: bool, default: scalar|null, auto_increment: bool}>  $columns
     * @return list<string>
     */
    private function redactedColumns(string $table, array $columns): array
    {
        $redactedColumns = [];

        foreach ($columns as $column) {
            if ($this->isSensitiveColumn($table, $column['name'])) {
                $redactedColumns[] = $column['name'];
            }
        }

        return $redactedColumns;
    }

    private function isSensitiveColumn(string $table, string $column): bool
    {
        $normalizedColumn = Str::snake($column);

        if (in_array($normalizedColumn, self::SENSITIVE_EXACT_COLUMNS, true)) {
            return true;
        }

        foreach (self::SENSITIVE_COLUMN_FRAGMENTS as $fragment) {
            if (str_contains($normalizedColumn, $fragment)) {
                return true;
            }
        }

        return in_array($normalizedColumn, self::GENERIC_SECRET_COLUMNS, true)
            && $this->isSensitiveTable($table);
    }

    private function isSensitiveTable(string $table): bool
    {
        return array_any(
            explode('_', Str::snake($table)),
            fn (string $segment): bool => in_array(Str::singular($segment), self::SENSITIVE_TABLE_FRAGMENTS, true),
        );
    }

    /**
     * @param  list<string>  $redactedColumns
     * @return array<string, scalar|null>
     */
    private function record(object $record, array $redactedColumns): array
    {
        $values = [];

        foreach ((array) $record as $column => $value) {
            $column = (string) $column;
            $values[$column] = in_array($column, $redactedColumns, true) && $value !== null
                ? self::REDACTED_VALUE
                : $this->scalarOrNull($value);
        }

        return $values;
    }

    /**
     * @param  array<mixed>  $records
     * @param  list<string>  $redactedColumns
     * @return list<array<string, scalar|null>>
     */
    private function recordsData(array $records, array $redactedColumns): array
    {
        $data = [];

        foreach ($records as $record) {
            if (is_object($record)) {
                $data[] = $this->record($record, $redactedColumns);
            }
        }

        return $data;
    }

    /**
     * @return list<string>
     */
    private function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $strings = [];

        foreach ($value as $item) {
            if (is_scalar($item)) {
                $strings[] = (string) $item;
            }
        }

        return $strings;
    }

    private function scalarOrNull(mixed $value): string|int|float|bool|null
    {
        if (is_scalar($value) || $value === null) {
            return $value;
        }

        return json_encode($value, JSON_THROW_ON_ERROR);
    }
}
