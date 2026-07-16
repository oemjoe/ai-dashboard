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
        Schema::create('data_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('system_name');
            $table->string('db_driver');
            $table->string('host')->nullable();
            $table->string('host_masked')->nullable();
            $table->unsignedInteger('port')->default(3306);
            $table->string('database_name')->nullable();
            $table->string('username')->nullable();
            $table->string('password_env_key')->nullable();
            $table->json('module_slugs')->nullable();
            $table->json('options')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_read_only_validated')->default(false);
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_failure_at')->nullable();
            $table->text('last_failure_message')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_connections');
    }
};
