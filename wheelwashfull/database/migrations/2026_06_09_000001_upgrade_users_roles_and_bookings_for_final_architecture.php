<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'service_mode')) {
                $table->string('service_mode')->default('partner_center')->after('service_id');
            }
            if (!Schema::hasColumn('bookings', 'worker_id')) {
                $table->foreignId('worker_id')->nullable()->after('partner_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('bookings', 'pickup_driver_id')) {
                $table->foreignId('pickup_driver_id')->nullable()->after('worker_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('bookings', 'total_amount')) {
                $table->decimal('total_amount', 10, 2)->nullable()->after('final_price');
            }
        });

        DB::table('bookings')
            ->whereNull('total_amount')
            ->update(['total_amount' => DB::raw('COALESCE(final_price, price, 0)')]);
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            foreach (['pickup_driver_id', 'worker_id', 'service_mode', 'total_amount'] as $column) {
                if (Schema::hasColumn('bookings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
