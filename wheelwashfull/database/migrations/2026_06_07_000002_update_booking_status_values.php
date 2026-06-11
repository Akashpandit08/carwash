<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('status', 30)->default('pending')->change();
        });

        DB::table('bookings')->where('status', 'in_progress')->update(['status' => 'started']);
    }

    public function down(): void
    {
        DB::table('bookings')->where('status', 'started')->update(['status' => 'in_progress']);

        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('status', ['pending', 'confirmed', 'assigned', 'in_progress', 'completed', 'cancelled'])
                ->default('pending')
                ->change();
        });
    }
};
