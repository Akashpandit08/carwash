<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('pickup_address_id')->nullable()->after('address')->constrained('addresses')->nullOnDelete();
            $table->foreignId('drop_address_id')->nullable()->after('pickup_address_id')->constrained('addresses')->nullOnDelete();
            $table->decimal('pickup_fee', 10, 2)->default(0)->after('price');
            $table->decimal('drop_fee', 10, 2)->default(0)->after('pickup_fee');
            $table->timestamp('pickup_scheduled_at')->nullable()->after('booking_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['pickup_address_id']);
            $table->dropForeign(['drop_address_id']);
            $table->dropColumn(['pickup_address_id', 'drop_address_id', 'pickup_fee', 'drop_fee', 'pickup_scheduled_at']);
        });
    }
};
