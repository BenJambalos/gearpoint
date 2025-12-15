<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('categories', 'has_expiry')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->boolean('has_expiry')->default(true)->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('categories', 'has_expiry')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('has_expiry');
            });
        }
    }
};
