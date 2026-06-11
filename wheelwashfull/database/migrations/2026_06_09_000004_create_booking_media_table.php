<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('booking_media')) {
            Schema::create('booking_media', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
                $table->foreignId('uploaded_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('type');
                $table->string('file_path');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_media');
    }
};
