<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_devices') && Schema::hasColumn('user_devices', 'role')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE user_devices MODIFY role VARCHAR(40) NOT NULL");
            }
        }

        if (Schema::hasTable('notifications') && ! Schema::hasColumn('notifications', 'event_type')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->string('event_type')->nullable()->after('type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('notifications') && Schema::hasColumn('notifications', 'event_type')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropColumn('event_type');
            });
        }
    }
};
