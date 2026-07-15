<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\EntryTransition;
use App\Models\Entry;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ChangeEntryStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $entry = $this->route('entry');

        if (! $user instanceof User || ! $entry instanceof Entry) {
            return false;
        }

        $transition = EntryTransition::tryFrom($this->string('transition')->value());

        if ($transition === EntryTransition::Approve) {
            return ! $entry->isArchived() && $user->can('approve', $entry);
        }

        if ($transition === EntryTransition::Archive) {
            return ! $entry->isArchived() && $user->can('archive', $entry);
        }

        if ($transition === EntryTransition::Restore) {
            return $entry->isArchived() && $user->can('restore', $entry);
        }

        if ($transition === EntryTransition::Delete) {
            return $entry->isArchived() && $user->can('forceDelete', $entry);
        }

        return false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'transition' => ['required', Rule::enum(EntryTransition::class)],
            'confirmed' => ['exclude_unless:transition,delete', 'required', 'accepted'],
            'status' => ['prohibited'],
        ];
    }
}
