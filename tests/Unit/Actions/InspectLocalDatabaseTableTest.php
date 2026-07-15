<?php

declare(strict_types=1);

use App\Actions\InspectLocalDatabaseTable;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('normalizes malformed schema metadata and redacts sensitive values', function (): void {
    Schema::shouldReceive('getColumns')
        ->once()
        ->with('metadata_edge_cases')
        ->andReturn([
            'not-an-array',
            ['name' => []],
            [
                'name' => 'api_key',
                'type_name' => 'varchar',
                'default' => ['nested' => 'secret'],
            ],
            [
                'name' => 'metadata',
                'type' => new stdClass(),
                'type_name' => new stdClass(),
                'nullable' => true,
                'default' => ['nested' => true],
            ],
        ]);
    Schema::shouldReceive('getIndexes')
        ->once()
        ->with('metadata_edge_cases')
        ->andReturn([
            'not-an-array',
            [
                'name' => [],
                'columns' => 'not-an-array',
                'unique' => true,
            ],
            [
                'name' => 123,
                'columns' => ['api_key', 9, null, []],
                'primary' => true,
            ],
        ]);
    Schema::shouldReceive('getForeignKeys')
        ->once()
        ->with('metadata_edge_cases')
        ->andReturn([
            'not-an-array',
            [
                'name' => [],
                'columns' => 'not-an-array',
                'foreign_table' => 123,
                'foreign_columns' => ['id', 9, null, []],
            ],
        ]);

    $paginator = new LengthAwarePaginator([
        (object) [
            'api_key' => 'stored-secret',
            'metadata' => (object) ['nested' => true],
        ],
        'not-an-object',
    ], 2, 25, 1);
    $query = Mockery::mock(Builder::class);
    $query->expects('orderBy')->with('api_key')->andReturnSelf();
    $query->expects('paginate')->with(25)->andReturn($paginator);
    DB::shouldReceive('table')
        ->once()
        ->with('metadata_edge_cases')
        ->andReturn($query);

    $inspection = resolve(InspectLocalDatabaseTable::class)->handle('metadata_edge_cases');

    expect($inspection['columns'])->toBe([
        [
            'name' => 'api_key',
            'type' => 'varchar',
            'nullable' => false,
            'default' => '[censurado]',
            'auto_increment' => false,
        ],
        [
            'name' => 'metadata',
            'type' => 'unknown',
            'nullable' => true,
            'default' => '{"nested":true}',
            'auto_increment' => false,
        ],
    ])->and($inspection['indexes'])->toBe([
        [
            'name' => '',
            'columns' => [],
            'unique' => true,
            'primary' => false,
        ],
        [
            'name' => '123',
            'columns' => ['api_key', '9'],
            'unique' => false,
            'primary' => true,
        ],
    ])->and($inspection['foreign_keys'])->toBe([
        [
            'name' => '',
            'columns' => [],
            'foreign_table' => null,
            'foreign_columns' => ['id', '9'],
        ],
    ])->and($inspection['redacted_columns'])->toBe(['api_key'])
        ->and($inspection['records']['data'])->toBe([
            [
                'api_key' => '[censurado]',
                'metadata' => '{"nested":true}',
            ],
        ])
        ->and($inspection['records']['total'])->toBe(2);
});

it('rejects tables reported without columns', function (): void {
    Schema::shouldReceive('getColumns')
        ->once()
        ->with('empty_table')
        ->andReturn([]);
    Schema::shouldReceive('getIndexes')
        ->once()
        ->with('empty_table')
        ->andReturn([]);
    Schema::shouldReceive('getForeignKeys')
        ->once()
        ->with('empty_table')
        ->andReturn([]);

    $query = Mockery::mock(Builder::class);
    DB::shouldReceive('table')
        ->once()
        ->with('empty_table')
        ->andReturn($query);

    expect(fn (): array => resolve(InspectLocalDatabaseTable::class)->handle('empty_table'))
        ->toThrow(RuntimeException::class, 'Cannot inspect a table without columns.');
});
