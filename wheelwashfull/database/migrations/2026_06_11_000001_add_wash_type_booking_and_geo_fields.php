<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'wash_type')) {
                $table->string('wash_type')->nullable()->after('service_mode');
            }
        });

        Schema::table('addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('addresses', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('pincode');
            }
            if (!Schema::hasColumn('addresses', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
        });

        Schema::table('worker_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('worker_profiles', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('service_area');
            }
            if (!Schema::hasColumn('worker_profiles', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
        });

        Schema::table('pickup_driver_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('pickup_driver_profiles', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('service_area');
            }
            if (!Schema::hasColumn('pickup_driver_profiles', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pickup_driver_profiles', function (Blueprint $table) {
            foreach (['longitude', 'latitude'] as $column) {
                if (Schema::hasColumn('pickup_driver_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('worker_profiles', function (Blueprint $table) {
            foreach (['longitude', 'latitude'] as $column) {
                if (Schema::hasColumn('worker_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('addresses', function (Blueprint $table) {
            foreach (['longitude', 'latitude'] as $column) {
                if (Schema::hasColumn('addresses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'wash_type')) {
                $table->dropColumn('wash_type');
            }
        });
    }
};
