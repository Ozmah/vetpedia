<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->strict()->ignoring('App\Models');
arch()->preset()->laravel();
arch()->preset()->security()->ignoring([
    'assert',
]);

arch('models use strict types')
    ->expect('App\Models')
    ->toUseStrictTypes();

arch('models use strict equality')
    ->expect('App\Models')
    ->toUseStrictEquality();

arch('models are final')
    ->expect('App\Models')
    ->classes()
    ->toBeFinal();

arch('models are not abstract')
    ->expect('App\Models')
    ->classes()
    ->not->toBeAbstract();

arch('controllers')
    ->expect('App\Http\Controllers')
    ->not->toBeUsed();

//
