<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Species;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class InitialSpeciesSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        foreach ($this->initialSpecies() as $slug => $name) {
            Species::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name],
            );
        }
    }

    /**
     * @return array<string, string>
     */
    private function initialSpecies(): array
    {
        return [
            'gatos' => 'Gatos',
            'perros' => 'Perros',
        ];
    }
}
