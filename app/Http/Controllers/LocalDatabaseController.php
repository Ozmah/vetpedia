<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\InspectLocalDatabaseTable;
use App\Actions\ListLocalDatabaseTables;
use Inertia\Inertia;
use Inertia\Response;

final readonly class LocalDatabaseController
{
    public function __construct(
        private ListLocalDatabaseTables $listTables,
        private InspectLocalDatabaseTable $inspectTable,
    ) {
        //
    }

    public function index(): Response
    {
        return Inertia::render('local/database', [
            'tables' => $this->listTables->handle(),
            'selectedTable' => null,
        ]);
    }

    public function show(string $table): Response
    {
        abort_unless($this->listTables->exists($table), 404);

        return Inertia::render('local/database', [
            'tables' => $this->listTables->handle(),
            'selectedTable' => $this->inspectTable->handle($table),
        ]);
    }
}
