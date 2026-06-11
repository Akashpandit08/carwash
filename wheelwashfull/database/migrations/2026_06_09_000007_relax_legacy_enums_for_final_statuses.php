<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE bookings MODIFY status VARCHAR(60) NOT NULL DEFAULT 'pending'");
            DB::statement("ALTER TABLE bookings MODIFY payment_status VARCHAR(60) NOT NULL DEFAULT 'pending'");
            DB::statement('ALTER TABLE booking_assignments MODIFY partner_id BIGINT UNSIGNED NULL');
            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'status')) {
                $table->string('status', 60)->default('pending')->change();
            }
            if (Schema::hasColumn('bookings', 'payment_status')) {
                $table->string('payment_status', 60)->default('pending')->change();
            }
        });

        Schema::table('booking_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('booking_assignments', 'partner_id')) {
                $table->unsignedBigInteger('partner_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        //
    }
};
