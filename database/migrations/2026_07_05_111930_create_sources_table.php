<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sources', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type')->index();
            $table->string('title');
            $table->text('authors')->nullable();
            $table->string('edition')->nullable();
            $table->unsignedSmallInteger('year')->nullable()->index();
            $table->string('publisher')->nullable();
            $table->string('isbn')->nullable()->index();
            $table->string('doi')->nullable()->index();
            $table->text('url')->nullable();
            $table->string('language', 16)->nullable()->index();
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};
