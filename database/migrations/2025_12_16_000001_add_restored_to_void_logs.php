<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'restored' to void_logs.action enum
        try {
            DB::statement("ALTER TABLE `void_logs` MODIFY `action` ENUM('requested','approved','rejected','cancelled','restored') NOT NULL;");
        } catch (\Exception $e) {
            // If the DB driver doesn't support ENUM modification in this environment, log and continue
            \Log::warning('Could not modify void_logs.action enum: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE `void_logs` MODIFY `action` ENUM('requested','approved','rejected','cancelled') NOT NULL;");
        } catch (\Exception $e) {
            \Log::warning('Could not revert void_logs.action enum: ' . $e->getMessage());
        }
    }
};