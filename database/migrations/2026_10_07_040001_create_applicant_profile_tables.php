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
        Schema::create('detail_persons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->unique()->constrained('persons')->cascadeOnDelete();
            $table->string('gender')->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->text('full_address')->nullable();
            $table->text('education_history')->nullable();
            $table->text('experience')->nullable();
            $table->text('skills')->nullable();
            $table->string('portfolio_link')->nullable();
            $table->timestamps();
        });

        Schema::create('person_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('persons')->cascadeOnDelete();
            $table->string('institution');
            $table->string('degree')->nullable();
            $table->string('major')->nullable();
            $table->unsignedSmallInteger('start_year')->nullable();
            $table->unsignedSmallInteger('end_year')->nullable();
            $table->decimal('gpa', 3, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('person_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('persons')->cascadeOnDelete();
            $table->string('company');
            $table->string('position');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('person_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('persons')->cascadeOnDelete();
            $table->string('skill_name');
            $table->string('level')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('person_skills');
        Schema::dropIfExists('person_experiences');
        Schema::dropIfExists('person_educations');
        Schema::dropIfExists('detail_persons');
    }
};
