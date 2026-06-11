<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Upgrade notifications table with operational flow fields
        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'body')) {
                $table->text('body')->nullable()->after('message');
            }
            if (! Schema::hasColumn('notifications', 'target_role')) {
                $table->string('target_role')->nullable()->after('target_type');
            }
            if (! Schema::hasColumn('notifications', 'sound_id')) {
                $table->unsignedBigInteger('sound_id')->nullable()->after('target_role');
                $table->foreign('sound_id')->references('id')->on('notification_sounds')->nullOnDelete();
            }
            if (! Schema::hasColumn('notifications', 'data')) {
                $table->json('data')->nullable()->after('sound_id');
            }
            if (! Schema::hasColumn('notifications', 'screen')) {
                $table->string('screen')->nullable()->after('data');
            }
        });

        // Add indexes to notifications
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'booking_id')) {
                try {
                    $table->index('booking_id', 'notifications_booking_id_index');
                } catch (\Exception $e) {
                    // Index may already exist
                }
            }
            if (Schema::hasColumn('notifications', 'type')) {
                try {
                    $table->index('type', 'notifications_type_index');
                } catch (\Exception $e) {
                    // Index may already exist
                }
            }
            if (Schema::hasColumn('notifications', 'target_role')) {
                try {
                    $table->index('target_role', 'notifications_target_role_index');
                } catch (\Exception $e) {
                    // Index may already exist
                }
            }
        });

        // Upgrade notification_users table with read tracking
        Schema::table('notification_users', function (Blueprint $table) {
            if (! Schema::hasColumn('notification_users', 'role')) {
                $table->string('role')->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('notification_users', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('status');
            }
            if (! Schema::hasColumn('notification_users', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('is_read');
            }
        });

        // Add index to notification_users
        Schema::table('notification_users', function (Blueprint $table) {
            try {
                $table->index(['user_id', 'role'], 'notification_users_user_id_role_index');
            } catch (\Exception $e) {
                // Index may already exist
            }
        });

        // Upgrade user_devices table with unified device_token field
        Schema::table('user_devices', function (Blueprint $table) {
            if (! Schema::hasColumn('user_devices', 'device_token')) {
                $table->string('device_token')->nullable()->after('role');
            }
            if (! Schema::hasColumn('user_devices', 'platform')) {
                $table->string('platform')->nullable()->after('device_token');
            }
        });

        // Add index to user_devices
        Schema::table('user_devices', function (Blueprint $table) {
            try {
                $table->index(['user_id', 'role'], 'user_devices_user_id_role_index');
            } catch (\Exception $e) {
                // Index may already exist
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_devices', function (Blueprint $table) {
            try {
                $table->dropIndex('user_devices_user_id_role_index');
            } catch (\Exception $e) {}
            if (Schema::hasColumn('user_devices', 'platform')) {
                $table->dropColumn('platform');
            }
            if (Schema::hasColumn('user_devices', 'device_token')) {
                $table->dropColumn('device_token');
            }
        });

        Schema::table('notification_users', function (Blueprint $table) {
            try {
                $table->dropIndex('notification_users_user_id_role_index');
            } catch (\Exception $e) {}
            if (Schema::hasColumn('notification_users', 'read_at')) {
                $table->dropColumn('read_at');
            }
            if (Schema::hasColumn('notification_users', 'is_read')) {
                $table->dropColumn('is_read');
            }
            if (Schema::hasColumn('notification_users', 'role')) {
                $table->dropColumn('role');
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            try {
                $table->dropIndex('notifications_target_role_index');
                $table->dropIndex('notifications_type_index');
                $table->dropIndex('notifications_booking_id_index');
            } catch (\Exception $e) {}
            if (Schema::hasColumn('notifications', 'screen')) {
                $table->dropColumn('screen');
            }
            if (Schema::hasColumn('notifications', 'data')) {
                $table->dropColumn('data');
            }
            if (Schema::hasColumn('notifications', 'sound_id')) {
                $table->dropForeign(['sound_id']);
                $table->dropColumn('sound_id');
            }
            if (Schema::hasColumn('notifications', 'target_role')) {
                $table->dropColumn('target_role');
            }
            if (Schema::hasColumn('notifications', 'body')) {
                $table->dropColumn('body');
            }
        });
    }
};
