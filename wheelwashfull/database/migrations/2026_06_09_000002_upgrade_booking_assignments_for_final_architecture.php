<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_assignments', 'worker_id')) {
                $table->foreignId('worker_id')->nullable()->after('partner_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('booking_assignments', 'pickup_driver_id')) {
                $table->foreignId('pickup_driver_id')->nullable()->after('worker_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('booking_assignments', 'assigned_by_admin_id')) {
                $table->foreignId('assigned_by_admin_id')->nullable()->after('pickup_driver_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('booking_assignments', 'status')) {
                $table->string('status')->default('active')->after('assigned_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_assignments', function (Blueprint $table) {
            foreach (['status', 'assigned_by_admin_id', 'pickup_driver_id', 'worker_id'] as $column) {
                if (Schema::hasColumn('booking_assignments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
