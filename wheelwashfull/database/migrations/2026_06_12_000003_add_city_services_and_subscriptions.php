<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (! Schema::hasColumn('services', 'service_area')) {
                $table->string('service_area')->nullable()->after('service_zone_id');
            }
            if (! Schema::hasColumn('services', 'is_global')) {
                $table->boolean('is_global')->default(false)->after('service_area');
            }
            if (! Schema::hasColumn('services', 'status')) {
                $table->string('status')->default('active')->after('is_active');
            }
            if (! Schema::hasColumn('services', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('status');
            }
        });

        if (! Schema::hasTable('subscription_plans')) {
            Schema::create('subscription_plans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_city_id')->nullable()->constrained('service_cities')->nullOnDelete();
                $table->foreignId('service_zone_id')->nullable()->constrained('service_zones')->nullOnDelete();
                $table->string('service_area')->nullable();
                $table->boolean('is_global')->default(false);
                $table->string('name');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2);
                $table->integer('duration_days')->default(30);
                $table->integer('total_washes');
                $table->integer('exterior_washes')->default(0);
                $table->integer('interior_washes')->default(0);
                $table->integer('foam_washes')->default(0);
                $table->boolean('tyre_polish_included')->default(false);
                $table->boolean('dashboard_wipe_included')->default(false);
                $table->boolean('vacuum_included')->default(false);
                $table->boolean('priority_booking')->default(false);
                $table->boolean('pickup_drop_included')->default(false);
                $table->boolean('doorstep_included')->default(true);
                $table->integer('max_washes_per_week')->nullable();
                $table->text('terms')->nullable();
                $table->string('status')->default('active');
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();
                $table->index(['slug', 'service_city_id', 'service_zone_id', 'status'], 'subscription_plans_scope_index');
            });
        }

        if (! Schema::hasTable('customer_subscriptions')) {
            Schema::create('customer_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->restrictOnDelete();
                $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
                $table->foreignId('customer_address_id')->nullable()->constrained('addresses')->nullOnDelete();
                $table->foreignId('service_city_id')->nullable()->constrained('service_cities')->nullOnDelete();
                $table->foreignId('service_zone_id')->nullable()->constrained('service_zones')->nullOnDelete();
                $table->string('service_area')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->integer('total_washes');
                $table->integer('used_washes')->default(0);
                $table->integer('remaining_washes');
                $table->integer('exterior_remaining')->default(0);
                $table->integer('interior_remaining')->default(0);
                $table->integer('foam_remaining')->default(0);
                $table->string('payment_status')->default('pending');
                $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
                $table->string('razorpay_order_id')->nullable();
                $table->string('razorpay_payment_id')->nullable();
                $table->string('status')->default('pending');
                $table->boolean('auto_renew')->default(false);
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('subscription_bookings')) {
            Schema::create('subscription_bookings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_subscription_id')->constrained('customer_subscriptions')->cascadeOnDelete();
                $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
                $table->string('wash_type');
                $table->timestamp('used_at')->nullable();
                $table->string('status')->default('reserved');
                $table->timestamps();
                $table->unique('booking_id');
            });
        }

        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'customer_subscription_id')) {
                $table->foreignId('customer_subscription_id')->nullable()->after('user_id')->constrained('customer_subscriptions')->nullOnDelete();
            }
            if (! Schema::hasColumn('bookings', 'booking_source')) {
                $table->string('booking_source')->default('normal')->after('customer_subscription_id');
            }
            if (! Schema::hasColumn('bookings', 'subscription_wash_type')) {
                $table->string('subscription_wash_type')->nullable()->after('booking_source');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_bookings');
        Schema::dropIfExists('customer_subscriptions');
        Schema::dropIfExists('subscription_plans');

        Schema::table('bookings', function (Blueprint $table) {
            foreach (['subscription_wash_type', 'booking_source', 'customer_subscription_id'] as $column) {
                if (Schema::hasColumn('bookings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('services', function (Blueprint $table) {
            foreach (['sort_order', 'status', 'is_global', 'service_area'] as $column) {
                if (Schema::hasColumn('services', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
