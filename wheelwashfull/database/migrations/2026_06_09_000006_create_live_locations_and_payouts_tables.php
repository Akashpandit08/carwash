<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('live_locations')) {
            Schema::create('live_locations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('booking_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('role');
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
                $table->decimal('heading', 8, 2)->nullable();
                $table->decimal('speed', 8, 2)->nullable();
                $table->timestamp('recorded_at');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('payouts')) {
            Schema::create('payouts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('role');
                $table->decimal('gross_amount', 10, 2);
                $table->decimal('commission_amount', 10, 2)->default(0);
                $table->decimal('net_amount', 10, 2);
                $table->string('payout_status')->default('pending');
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('live_locations');
    }
};
