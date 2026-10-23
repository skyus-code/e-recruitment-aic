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
        Schema::create('cv_parsed_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_file_id')->unique()->constrained('cv_files')->cascadeOnDelete();
            $table->longText('raw_text')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('total_words')->default(0);
            $table->unsignedInteger('total_characters')->default(0);
            $table->enum('parsing_status', ['success', 'failed'])->default('success');
            $table->text('error_message')->nullable();
            $table->string('parser_version')->nullable();
            $table->timestamp('parsed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('data_stemmer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_id')->constrained('job_postings')->cascadeOnDelete();
            $table->string('original_word');
            $table->string('stemmed_word')->index();
            $table->string('word_category')->default('general');
            $table->boolean('is_matched_with_job')->default(false);
            $table->unsignedInteger('frequency_in_cv')->default(1);
            $table->timestamps();
        });

        Schema::create('score_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_keyword_id')->nullable()->constrained('job_keywords')->nullOnDelete();
            $table->string('keyword');
            $table->string('stemmed_keyword')->nullable();
            $table->boolean('is_matched')->default(false);
            $table->decimal('matched_score', 5, 2)->default(0);
            $table->string('found_in_section')->nullable();
            $table->timestamps();
        });

        Schema::create('interview_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->date('interview_date');
            $table->time('interview_time');
            $table->string('interview_type')->default('online');
            $table->string('interview_location');
            $table->string('interviewer_name')->nullable();
            $table->string('status')->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'interview_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_schedules');
        Schema::dropIfExists('score_details');
        Schema::dropIfExists('data_stemmer');
        Schema::dropIfExists('cv_parsed_data');
    }
};
