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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_id')->constrained('job_postings')->cascadeOnDelete();
            $table->string('cv_path')->nullable();
            $table->longText('cv_text')->nullable();
            $table->json('matched_keywords')->nullable();
            $table->enum('status', ['applied', 'screening', 'passed', 'interview', 'hired', 'rejected'])->default('applied');
            $table->decimal('score', 5, 2)->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'job_id']);
        });

        Schema::create('cv_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type');
            $table->string('file_hash', 64);
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('degree_certification_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type');
            $table->string('file_hash', 64);
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('degree_certification_files');
        Schema::dropIfExists('cv_files');
        Schema::dropIfExists('applications');
    }
};
