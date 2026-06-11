<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('banners')) {
            Schema::create('banners', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('subtitle')->nullable();
                $table->string('image');
                $table->enum('redirect_type', ['home', 'services', 'service_detail', 'booking', 'booking_detail', 'offers', 'profile', 'external_url', 'custom_screen'])->default('home');
                $table->string('redirect_value')->nullable();
                $table->enum('user_type', ['all', 'customer', 'partner', 'driver', 'worker'])->default('all');
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->dateTime('start_date')->nullable();
                $table->dateTime('end_date')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                if (! Schema::hasColumn('notifications', 'title')) {
                    $table->string('title')->nullable()->after('id');
                }
                if (! Schema::hasColumn('notifications', 'image')) {
                    $table->string('image')->nullable()->after('message');
                }
                if (! Schema::hasColumn('notifications', 'target_type')) {
                    $table->enum('target_type', ['all', 'customer', 'partner', 'driver', 'worker', 'selected_users'])->nullable()->after('image');
                }
                if (! Schema::hasColumn('notifications', 'redirect_type')) {
                    $table->enum('redirect_type', ['home', 'services', 'service_detail', 'booking', 'booking_detail', 'offers', 'profile', 'external_url', 'custom_screen'])->default('home')->after('target_type');
                }
                if (! Schema::hasColumn('notifications', 'redirect_value')) {
                    $table->string('redirect_value')->nullable()->after('redirect_type');
                }
                if (! Schema::hasColumn('notifications', 'send_type')) {
                    $table->enum('send_type', ['immediate', 'scheduled'])->nullable()->after('redirect_value');
                }
                if (! Schema::hasColumn('notifications', 'scheduled_at')) {
                    $table->dateTime('scheduled_at')->nullable()->after('send_type');
                }
                if (! Schema::hasColumn('notifications', 'sent_at')) {
                    $table->dateTime('sent_at')->nullable()->after('scheduled_at');
                }
                if (! Schema::hasColumn('notifications', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->after('sent_at')->constrained('users')->nullOnDelete();
                }
            });
        }

        if (! Schema::hasTable('notification_users')) {
            Schema::create('notification_users', function (Blueprint $table) {
                $table->id();
                $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
                $table->text('error_message')->nullable();
                $table->dateTime('sent_at')->nullable();
                $table->timestamps();
                $table->unique(['notification_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('user_devices')) {
            Schema::create('user_devices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->enum('role', ['customer', 'partner', 'driver', 'worker']);
                $table->string('expo_push_token')->nullable();
                $table->string('fcm_token')->nullable();
                $table->string('device_type')->nullable();
                $table->string('device_name')->nullable();
                $table->boolean('is_active')->default(true);
                $table->dateTime('last_used_at')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'is_active']);
                $table->index(['role', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
        Schema::dropIfExists('notification_users');
        Schema::dropIfExists('banners');
    }
};
