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
        Schema::create('interview_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('assessor_name')->nullable();
            $table->date('assessed_at')->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->enum('decision', ['lulus', 'dipertimbangkan', 'tidak_lulus'])->nullable();
            $table->text('conclusion')->nullable();
            $table->timestamps();
        });

        Schema::create('interview_assessment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_assessment_id')->constrained('interview_assessments')->cascadeOnDelete();
            $table->string('aspect', 50);
            $table->decimal('weight', 5, 2);
            $table->unsignedTinyInteger('score')->nullable();
            $table->decimal('value', 6, 2)->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['interview_assessment_id', 'aspect']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_assessment_items');
        Schema::dropIfExists('interview_assessments');
    }
};
