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
        Schema::create('omneaxa_whatsapp_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('role')->nullable();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->string('phone')->nullable();
            $table->string('template_name')->nullable();
            $table->string('event_type')->nullable();
            $table->string('module')->nullable();
            $table->string('endpoint')->nullable();
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->enum('status', ['skipped', 'sent', 'failed'])->default('failed');
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('omneaxa_whatsapp_attempts');
    }
};
