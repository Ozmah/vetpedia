<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entry_species', function (Blueprint $table): void {
            $table->foreignUuid('entry_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('species_id')->constrained('species')->restrictOnDelete();
            $table->timestamps();

            $table->primary(['entry_id', 'species_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entry_species');
    }
};
