<?php

namespace App\Models;

use App\Enums\ApplicationStatusEnum;
use Database\Factories\ApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Application extends Model
{
    /** @use HasFactory<ApplicationFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatusEnum::class,
            'score' => 'decimal:2',
            'applied_at' => 'datetime',
            'admin_seen_at' => 'datetime',
            'matched_keywords' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class, 'job_id');
    }

    public function cvFile(): HasOne
    {
        return $this->hasOne(CvFile::class);
    }

    public function degreeCertificationFile(): HasOne
    {
        return $this->hasOne(DegreeCertificationFile::class);
    }

    public function parsedData(): HasOneThrough
    {
        return $this->hasOneThrough(CvParsedData::class, CvFile::class);
    }

    public function stemmerData(): HasMany
    {
        return $this->hasMany(DataStemmer::class);
    }

    public function scoreDetails(): HasMany
    {
        return $this->hasMany(ScoreDetail::class);
    }

    public function interviewSchedules(): HasMany
    {
        return $this->hasMany(InterviewSchedule::class);
    }

    public function interviewSchedule(): HasOne
    {
        return $this->hasOne(InterviewSchedule::class)
            ->ofMany(['interview_date' => 'max', 'id' => 'max']);
    }

    public function assessment(): HasOne
    {
        return $this->hasOne(InterviewAssessment::class);
    }
}
