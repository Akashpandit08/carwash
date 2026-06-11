<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Drop global unique index
            $table->dropUnique('vehicles_registration_number_unique');
            
            // Add scoped unique index
            $table->unique(['user_id', 'registration_number'], 'vehicles_user_reg_unique');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Drop scoped unique index
            $table->dropUnique('vehicles_user_reg_unique');
            
            // Restore global unique index
            $table->unique('registration_number');
        });
    }
};
