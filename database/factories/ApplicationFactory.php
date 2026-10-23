<?php

namespace Database\Factories;

use App\Enums\ApplicationStatusEnum;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'job_id' => JobPosting::factory(),
            'status' => ApplicationStatusEnum::Applied,
            'applied_at' => now(),
        ];
    }

    /**
     * Indicate that the application has been screened by the NLP pipeline.
     */
    public function scored(float $score = 80.0): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ApplicationStatusEnum::Screening,
            'score' => $score,
        ]);
    }
}
