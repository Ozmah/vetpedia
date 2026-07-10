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

        return match ($transition) {
            EntryTransition::Approve => ! $entry->isArchived() && $user->can('approve', $entry),
            EntryTransition::Archive => ! $entry->isArchived() && $user->can('archive', $entry),
            EntryTransition::Restore => $entry->isArchived() && $user->can('restore', $entry),
            EntryTransition::Delete => $entry->isArchived() && $user->can('forceDelete', $entry),
            null => $this->canChangeEntryStatus($user, $entry),
        };
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

    private function canChangeEntryStatus(User $user, Entry $entry): bool
    {
        if ($user->can('approve', $entry)) {
            return true;
        }

        if ($user->can('archive', $entry)) {
            return true;
        }

        if ($user->can('restore', $entry)) {
            return true;
        }

        return $user->can('forceDelete', $entry);
    }
}
