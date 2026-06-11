<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('worker_profiles')) {
            Schema::create('worker_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('partner_id')->nullable()->constrained('users')->nullOnDelete();
                $table->json('skills')->nullable();
                $table->string('service_area')->nullable();
                $table->string('current_status')->default('available');
                $table->decimal('rating', 3, 2)->nullable();
                $table->unsignedInteger('total_jobs')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pickup_driver_profiles')) {
            Schema::create('pickup_driver_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('partner_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('vehicle_type')->nullable();
                $table->string('license_number')->nullable();
                $table->string('service_area')->nullable();
                $table->string('current_status')->default('available');
                $table->decimal('rating', 3, 2)->nullable();
                $table->unsignedInteger('total_jobs')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('partner_profiles')) {
            Schema::create('partner_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('business_name');
                $table->text('address')->nullable();
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->string('service_area')->nullable();
                $table->string('current_status')->default('active');
                $table->string('commission_type')->default('percentage');
                $table->decimal('commission_value', 10, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_profiles');
        Schema::dropIfExists('pickup_driver_profiles');
        Schema::dropIfExists('worker_profiles');
    }
};
