<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entry_aliases', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('entry_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('normalized_name');
            $table->timestamps();

            $table->unique(['entry_id', 'normalized_name']);
            $table->index('normalized_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entry_aliases');
    }
};
