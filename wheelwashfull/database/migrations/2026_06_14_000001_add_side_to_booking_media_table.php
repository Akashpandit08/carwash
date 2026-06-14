<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('booking_media') && ! Schema::hasColumn('booking_media', 'side')) {
            Schema::table('booking_media', function (Blueprint $table) {
                $table->string('side')->nullable()->after('type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_media') && Schema::hasColumn('booking_media', 'side')) {
            Schema::table('booking_media', function (Blueprint $table) {
                $table->dropColumn('side');
            });
        }
    }
};
