<?php

namespace App\Models;

use App\Enums\JobStatusEnum;
use App\Enums\JobTypeEnum;
use Database\Factories\JobPostingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPosting extends Model
{
    /** @use HasFactory<JobPostingFactory> */
    use HasFactory;

    protected $table = 'job_postings';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => JobStatusEnum::class,
            'job_type' => JobTypeEnum::class,
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(JobKeyword::class, 'job_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'job_id');
    }

    public function isOpen(): bool
    {
        return $this->status === JobStatusEnum::Open;
    }

    public function salaryRange(): ?string
    {
        if (! $this->salary_min && ! $this->salary_max) {
            return null;
        }

        return 'Rp '.number_format((int) $this->salary_min, 0, ',', '.').' - Rp '.number_format((int) $this->salary_max, 0, ',', '.');
    }
}
