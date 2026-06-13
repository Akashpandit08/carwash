<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_locations', function (Blueprint $table) {
            if (!Schema::hasColumn('live_locations', 'is_online')) {
                $table->boolean('is_online')->default(true)->after('role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('live_locations', function (Blueprint $table) {
            if (Schema::hasColumn('live_locations', 'is_online')) {
                $table->dropColumn('is_online');
            }
        });
    }
};
