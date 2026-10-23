<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewAssessmentItem extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'value' => 'decimal:2',
            'score' => 'integer',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(InterviewAssessment::class, 'interview_assessment_id');
    }
}
