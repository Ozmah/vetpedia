<?php

declare(strict_types=1);

namespace App\Rules;

use App\Actions\NormalizeEntryAliasName;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

final class DistinctNormalizedEntryAlias implements DataAwareRule, ValidationRule
{
    /** @var array<string, mixed> */
    private array $data = [];

    public function __construct(private readonly NormalizeEntryAliasName $normalizeEntryAliasName)
    {
        //
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        $index = $this->aliasIndex($attribute);
        $aliases = $this->data['aliases'] ?? null;

        if ($index === null || ! is_array($aliases)) {
            return;
        }

        $normalizedValue = $this->normalizeEntryAliasName->handle($value);

        foreach (array_slice($aliases, 0, $index) as $alias) {
            if (is_string($alias) && $this->normalizeEntryAliasName->handle($alias) === $normalizedValue) {
                $fail('The alias must be unique after normalizing accents and whitespace.');

                return;
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    private function aliasIndex(string $attribute): ?int
    {
        $index = mb_strrchr($attribute, '.');

        if ($index === false || ! ctype_digit(mb_substr($index, 1))) {
            return null;
        }

        return (int) mb_substr($index, 1);
    }
}
