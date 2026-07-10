<?php

declare(strict_types=1);

use App\Enums\EntryStatus;
use App\Http\Requests\ChangeEntryStatusRequest;
use App\Http\Requests\StoreEntryRequest;
use App\Http\Requests\UpdateEntryRequest;
use App\Models\Entry;
use App\Models\Source;
use App\Models\Species;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

beforeEach(function (): void {
    Route::middleware('web')->group(function (): void {
        Route::post('/_testing/entries', fn (StoreEntryRequest $request) => response()->json($request->validated()));

        Route::patch('/_testing/entries/{entry}', fn (UpdateEntryRequest $request, Entry $entry) => response()->json($request->validated()));

        Route::post('/_testing/entries/{entry}/transition', fn (ChangeEntryStatusRequest $request, Entry $entry) => response()->json($request->validated()));
    });
});

it('validates and normalizes complete entry creation payloads', function (): void {
    $user = User::factory()->create();
    $species = Species::factory()->create();
    $source = Source::factory()->create();

    $response = $this->actingAs($user)->postJson('/_testing/entries', [
        'type' => 'drug',
        'title' => '  Meloxicam  ',
        'summary' => '   ',
        'warnings' => '  Monitor renal function.  ',
        'aliases' => ['  Metacam  ', ['name' => '  Meloxidyl  ']],
        'species' => [$species->id],
        'sources' => [
            ['id' => $source->id, 'locator' => 'p. 42', 'note' => 'Primary source.'],
        ],
        'sections' => [
            [
                'key' => 'description',
                'title' => 'Description',
                'body' => 'NSAID.',
                'sort_order' => 1,
                'sources' => [
                    ['id' => $source->id, 'locator' => 'p. 42'],
                ],
            ],
        ],
    ]);

    $response->assertOk()
        ->assertJsonPath('title', 'Meloxicam')
        ->assertJsonPath('summary', null)
        ->assertJsonPath('warnings', 'Monitor renal function.')
        ->assertJsonPath('aliases.0', 'Metacam')
        ->assertJsonPath('aliases.1', 'Meloxidyl');
});

it('rejects invalid nested entry payloads and privileged fields', function (): void {
    $user = User::factory()->create();
    $missingId = Str::uuid()->toString();

    $response = $this->actingAs($user)->postJson('/_testing/entries', [
        'type' => 'condition',
        'title' => '',
        'status' => 'vet_approved',
        'slug' => 'forged-slug',
        'created_by' => $user->id,
        'aliases' => ['Metacam', 'metacam'],
        'species' => [$missingId],
        'sources' => [
            ['id' => $missingId],
        ],
        'sections' => [
            ['key' => 'description', 'title' => 'One', 'body' => 'Body'],
            ['key' => 'description', 'title' => 'Two', 'body' => 'Body'],
        ],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'type',
            'title',
            'status',
            'slug',
            'created_by',
            'aliases.1',
            'species.0',
            'sources.0.id',
            'sections.1.key',
        ]);
});

it('rejects entry creation by suspended users before validation', function (): void {
    $user = User::factory()->suspended()->create();

    $this->actingAs($user)->postJson('/_testing/entries', [
        'type' => 'drug',
        'title' => 'Meloxicam',
    ])->assertRedirectToRoute('login');

    $this->assertGuest();
});

it('authorizes entry updates according to approval and archive state', function (): void {
    $user = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $draft = Entry::factory()->create();
    $approved = Entry::factory()->approved()->create();
    $archived = Entry::factory()->archived()->create();

    $this->actingAs($user)
        ->patchJson('/_testing/entries/'.$draft->id, ['title' => 'User update'])
        ->assertOk();

    $this->actingAs($user)
        ->patchJson('/_testing/entries/'.$approved->id, ['title' => 'Unauthorized'])
        ->assertForbidden();

    $this->actingAs($admin)
        ->patchJson('/_testing/entries/'.$approved->id, ['title' => 'Admin update'])
        ->assertOk();

    $this->actingAs($admin)
        ->patchJson('/_testing/entries/'.$archived->id, ['title' => 'Archived update'])
        ->assertForbidden();
});

it('validates explicit entry transitions without accepting arbitrary statuses', function (): void {
    $admin = User::factory()->admin()->create();
    $entry = Entry::factory()->status(EntryStatus::Documented)->create();

    $this->actingAs($admin)
        ->postJson(sprintf('/_testing/entries/%s/transition', $entry->id), [
            'transition' => 'approve',
        ])
        ->assertOk()
        ->assertJsonPath('transition', 'approve');

    $this->actingAs($admin)
        ->postJson(sprintf('/_testing/entries/%s/transition', $entry->id), [
            'transition' => 'publish',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('transition');

    $this->actingAs($admin)
        ->postJson(sprintf('/_testing/entries/%s/transition', $entry->id), [
            'transition' => 'approve',
            'status' => 'vet_approved',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');
});

it('requires permission and explicit confirmation for permanent deletion', function (): void {
    $user = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $entry = Entry::factory()->archived()->create();

    $this->actingAs($user)
        ->postJson(sprintf('/_testing/entries/%s/transition', $entry->id), [
            'transition' => 'delete',
            'confirmed' => true,
        ])
        ->assertForbidden();

    $this->actingAs($admin)
        ->postJson(sprintf('/_testing/entries/%s/transition', $entry->id), [
            'transition' => 'delete',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('confirmed');

    $this->actingAs($admin)
        ->postJson(sprintf('/_testing/entries/%s/transition', $entry->id), [
            'transition' => 'delete',
            'confirmed' => true,
        ])
        ->assertOk()
        ->assertJsonPath('confirmed', true);
});
