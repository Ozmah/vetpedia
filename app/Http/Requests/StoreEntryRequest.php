<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\EntryType;
use App\Models\Entry;
use App\Models\Source;
use App\Models\Species;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->can('create', Entry::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(EntryType::class)],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:5000'],
            'warnings' => ['nullable', 'string', 'max:10000'],

            'aliases' => ['sometimes', 'array', 'max:50'],
            'aliases.*' => ['required', 'string', 'max:255', 'distinct:ignore_case'],

            'species' => ['sometimes', 'array', 'max:50'],
            'species.*' => ['required', 'uuid', 'distinct', Rule::exists(Species::class, 'id')],

            'sources' => ['sometimes', 'array', 'max:100'],
            'sources.*' => ['array:id,locator,note'],
            'sources.*.id' => ['required', 'uuid', 'distinct', Rule::exists(Source::class, 'id')],
            'sources.*.locator' => ['nullable', 'string', 'max:255'],
            'sources.*.note' => ['nullable', 'string', 'max:5000'],

            'sections' => ['sometimes', 'array', 'max:50'],
            'sections.*' => ['array:key,title,body,sort_order,sources'],
            'sections.*.key' => ['required', 'string', 'alpha_dash:ascii', 'max:100', 'distinct'],
            'sections.*.title' => ['required', 'string', 'max:255'],
            'sections.*.body' => ['required', 'string', 'max:100000'],
            'sections.*.sort_order' => ['sometimes', 'integer', 'min:0'],
            'sections.*.sources' => ['sometimes', 'array', 'max:100'],
            'sections.*.sources.*' => ['array:id,locator,note'],
            'sections.*.sources.*.id' => ['required', 'uuid', Rule::exists(Source::class, 'id')],
            'sections.*.sources.*.locator' => ['nullable', 'string', 'max:255'],
            'sections.*.sources.*.note' => ['nullable', 'string', 'max:5000'],

            'status' => ['prohibited'],
            'slug' => ['prohibited'],
            'created_by' => ['prohibited'],
            'updated_by' => ['prohibited'],
            'approved_by' => ['prohibited'],
            'approved_at' => ['prohibited'],
            'archived_at' => ['prohibited'],
        ];
    }

    public function prepareForValidation(): void
    {
        $normalized = [];

        if (is_string($this->input('title'))) {
            $normalized['title'] = mb_trim($this->string('title')->value());
        }

        foreach (['summary', 'warnings'] as $field) {
            if (! is_string($this->input($field))) {
                continue;
            }

            $value = mb_trim($this->string($field)->value());
            $normalized[$field] = $value === '' ? null : $value;
        }

        if (is_array($this->input('aliases'))) {
            $normalized['aliases'] = array_map(
                $this->normalizeAlias(...),
                $this->input('aliases'),
            );
        }

        $this->merge($normalized);
    }

    private function normalizeAlias(mixed $alias): mixed
    {
        if (is_string($alias)) {
            return mb_trim($alias);
        }

        if (is_array($alias) && is_string($alias['name'] ?? null)) {
            return mb_trim($alias['name']);
        }

        return $alias;
    }
}
