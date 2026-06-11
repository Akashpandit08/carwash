<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'delivery_driver_id')) {
                $table->foreignId('delivery_driver_id')->nullable()->after('pickup_driver_id')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('booking_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_assignments', 'delivery_driver_id')) {
                $table->foreignId('delivery_driver_id')->nullable()->after('pickup_driver_id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('booking_assignments', 'delivery_driver_id')) {
                $table->dropConstrainedForeignId('delivery_driver_id');
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'delivery_driver_id')) {
                $table->dropConstrainedForeignId('delivery_driver_id');
            }
        });
    }
};
