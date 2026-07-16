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
        Schema::create('analysis_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('module_slug')->index();
            $table->string('period_label')->nullable();
            $table->json('filters')->nullable();
            $table->json('metrics')->nullable();
            $table->json('findings')->nullable();
            $table->string('mapping_status')->default('data_mapping_incomplete');
            $table->string('source_reference')->nullable();
            $table->timestamp('data_as_of')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_snapshots');
    }
};
