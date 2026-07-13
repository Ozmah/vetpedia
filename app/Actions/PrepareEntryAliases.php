<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Validation\ValidationException;

final readonly class PrepareEntryAliases
{
    public function __construct(private NormalizeEntryAliasName $normalizeEntryAliasName)
    {
        //
    }

    /**
     * @param  array<int, array{name: string}|string>  $aliases
     * @return list<array{name: string, normalized_name: string}>
     *
     * @throws ValidationException
     */
    public function handle(array $aliases): array
    {
        $prepared = [];
        $seen = [];

        foreach ($aliases as $alias) {
            $name = is_string($alias) ? $alias : $alias['name'];
            $normalizedName = $this->normalizeEntryAliasName->handle($name);

            if (array_key_exists($normalizedName, $seen)) {
                throw ValidationException::withMessages([
                    'aliases' => ['Aliases must be unique after normalizing accents and whitespace.'],
                ]);
            }

            $seen[$normalizedName] = true;
            $prepared[] = [
                'name' => $name,
                'normalized_name' => $normalizedName,
            ];
        }

        return $prepared;
    }
}
