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
        Schema::table('service_categories', function (Blueprint $table) {
            $table->foreignId('service_city_id')->nullable()->constrained('service_cities')->nullOnDelete();
            $table->foreignId('service_zone_id')->nullable()->constrained('service_zones')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropForeign(['service_city_id']);
            $table->dropForeign(['service_zone_id']);
            $table->dropColumn(['service_city_id', 'service_zone_id']);
        });
    }
};
