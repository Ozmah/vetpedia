<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('section_sources', function (Blueprint $table): void {
            $table->foreignUuid('entry_section_id')->constrained('entry_sections')->cascadeOnDelete();
            $table->foreignUuid('source_id')->constrained()->restrictOnDelete();
            $table->string('locator')->nullable();
            $table->text('note')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->primary(['entry_section_id', 'source_id']);
            $table->index(['source_id', 'entry_section_id']);
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_sources');
    }
};
