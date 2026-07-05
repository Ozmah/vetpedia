<?php

declare(strict_types=1);

use App\Enums\SourceType;
use App\Models\Source;
use App\Models\User;

it('casts source type values to the source type enum', function (): void {
    $source = Source::factory()->create([
        'type' => SourceType::Guideline,
    ]);

    expect($source->type)->toBe(SourceType::Guideline);
});

it('defines the canonical source type values', function (): void {
    expect(array_map(
        fn (SourceType $type): string => $type->value,
        SourceType::cases(),
    ))->toBe([
        'book',
        'article',
        'guideline',
        'website',
        'internal_note',
        'other',
    ]);
});

it('tracks the admin user that created the source', function (): void {
    $creator = User::factory()->admin()->create();

    $source = Source::factory()->create([
        'created_by' => $creator->id,
    ]);

    expect($source->createdBy->is($creator))->toBeTrue();
});

it('can represent book sources', function (): void {
    $source = Source::factory()->book()->create([
        'title' => 'Small Animal Internal Medicine',
        'authors' => 'Nelson; Couto',
        'edition' => '6th',
        'year' => 2020,
        'publisher' => 'Elsevier',
        'isbn' => '9780323676946',
        'language' => 'en',
    ]);

    expect($source->type)->toBe(SourceType::Book)
        ->and($source->title)->toBe('Small Animal Internal Medicine')
        ->and($source->isbn)->toBe('9780323676946');
});

it('can represent non-book sources', function (): void {
    $source = Source::factory()->website()->create([
        'title' => 'Veterinary guideline',
        'url' => 'https://example.com/guideline',
        'language' => 'en',
    ]);

    expect($source->type)->toBe(SourceType::Website)
        ->and($source->url)->toBe('https://example.com/guideline')
        ->and($source->isbn)->toBeNull();
});
