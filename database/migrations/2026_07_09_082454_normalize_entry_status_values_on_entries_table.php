<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            DB::table('entries')
                ->where('status', 'vet_approved')
                ->update(['status' => 'vet_approved']);

            DB::table('entries')
                ->whereIn('status', ['raw', 'rejected'])
                ->update(['status' => 'draft']);

            DB::table('entries')
                ->whereIn('status', ['parsed', 'needs_review', 'soft_approved'])
                ->update(['status' => 'documented']);

            Schema::table('entries', function (Blueprint $table): void {
                $table->string('status')->default('draft')->change();
            });
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            DB::table('entries')
                ->where('status', 'draft')
                ->update(['status' => 'raw']);

            DB::table('entries')
                ->where('status', 'documented')
                ->update(['status' => 'needs_review']);

            Schema::table('entries', function (Blueprint $table): void {
                $table->string('status')->default('raw')->change();
            });
        });
    }
};
