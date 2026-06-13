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
        Schema::table('services', function (Blueprint $table) {
            $table->string('wash_type')->nullable()->after('vehicle_types');
        });

        Schema::table('slots', function (Blueprint $table) {
            $table->string('wash_type')->nullable()->after('end_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('wash_type');
        });

        Schema::table('slots', function (Blueprint $table) {
            $table->dropColumn('wash_type');
        });
    }
};
