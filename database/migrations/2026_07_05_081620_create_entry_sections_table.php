<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entry_sections', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('entry_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('title');
            $table->text('body');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['entry_id', 'key']);
            $table->index(['entry_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entry_sections');
    }
};
