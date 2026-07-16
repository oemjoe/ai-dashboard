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
        Schema::create('ai_providers', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('driver');
            $table->string('model')->nullable();
            $table->string('base_url')->nullable();
            $table->string('api_key_env_key')->nullable();
            $table->unsignedSmallInteger('priority')->default(1);
            $table->unsignedSmallInteger('timeout_seconds')->default(15);
            $table->unsignedSmallInteger('max_retries')->default(1);
            $table->decimal('min_confidence', 4, 2)->default(0.75);
            $table->boolean('is_enabled')->default(true);
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_error_at')->nullable();
            $table->text('last_error_message')->nullable();
            $table->json('usage_meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_providers');
    }
};
