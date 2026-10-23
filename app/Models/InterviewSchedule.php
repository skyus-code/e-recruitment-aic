<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewSchedule extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['interview_date' => 'date'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
