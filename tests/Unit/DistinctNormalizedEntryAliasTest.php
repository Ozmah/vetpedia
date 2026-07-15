<?php

declare(strict_types=1);

use App\Rules\DistinctNormalizedEntryAlias;

it('ignores values that cannot be compared with preceding aliases', function (): void {
    $rule = resolve(DistinctNormalizedEntryAlias::class);
    $failed = false;
    $fail = function () use (&$failed): void {
        $failed = true;
    };

    $rule->validate('aliases.0', 123, $fail);
    $rule->setData([])->validate('aliases.0', 'Lasix', $fail);
    $rule->setData(['aliases' => ['Lasix']])->validate('aliases.invalid', 'Lasix', $fail);

    expect($failed)->toBeFalse();
});
