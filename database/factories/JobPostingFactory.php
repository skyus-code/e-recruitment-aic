<?php

namespace Database\Factories;

use App\Enums\JobStatusEnum;
use App\Enums\JobTypeEnum;
use App\Models\Department;
use App\Models\JobPosting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobPosting>
 */
class JobPostingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $minimum = fake()->numberBetween(3, 6) * 1_000_000;

        return [
            'department_id' => Department::factory(),
            'title' => 'Guru '.fake()->unique()->jobTitle(),
            'description' => fake()->paragraphs(3, true),
            'requirements' => fake()->paragraphs(2, true),
            'salary_min' => $minimum,
            'salary_max' => $minimum + 2_500_000,
            'location' => fake()->city(),
            'job_type' => JobTypeEnum::FullTime->value,
            'status' => JobStatusEnum::Open->value,
        ];
    }

    /**
     * Indicate that the vacancy has been closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => JobStatusEnum::Closed->value,
        ]);
    }
}
