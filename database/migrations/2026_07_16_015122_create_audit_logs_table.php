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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->json('role_names')->nullable();
            $table->string('company_code')->nullable();
            $table->string('module_slug')->nullable();
            $table->string('action');
            $table->text('question')->nullable();
            $table->text('query_template')->nullable();
            $table->string('database_name')->nullable();
            $table->string('table_name')->nullable();
            $table->unsignedInteger('rows_read')->nullable();
            $table->string('ai_provider')->nullable();
            $table->string('ai_model')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->string('status')->default('success');
            $table->string('error_code')->nullable();
            $table->string('route_name')->nullable();
            $table->string('method', 10)->nullable();
            $table->string('path')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('trace_id')->nullable()->index();
            $table->json('context')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
